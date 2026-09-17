#!/usr/bin/env python3
"""HTTP regression tests. Run only against a disposable local Zlar database."""
import http.cookiejar
import json
import os
import secrets
import urllib.error
import urllib.request

BASE = os.environ.get('BASE_URL', 'http://localhost:8080').rstrip('/')
RUN = secrets.token_hex(7)

def client():
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

def request(c, endpoint, data, status=200):
    req = urllib.request.Request(BASE + '/api/' + endpoint + '.php',
        json.dumps(data).encode(), {'Content-Type': 'application/json'})
    try:
        response = c.open(req)
    except urllib.error.HTTPError as exc:
        response = exc
    body = json.load(response)
    assert response.status == status, (endpoint, data, response.status, body)
    assert body.get('ok') is (status < 400), (endpoint, body)
    return body

def cpf():
    n = [secrets.randbelow(10) for _ in range(9)]
    for weights in (range(10, 1, -1), range(11, 1, -1)):
        n.append((sum(a*b for a,b in zip(n, weights))*10%11)%10)
    v = ''.join(map(str, n))
    return f'{v[:3]}.{v[3:6]}.{v[6:9]}-{v[9:]}'

def register(role, suffix):
    c = client()
    payload = dict(nome='Teste ' + suffix, email=f'{RUN}.{suffix}@teste.local',
        telefone='(41) 3333-1000', nascimento='1990-01-01',
        senha='Senha123', confirmar_senha='Senha123')
    if role == 'morador':
        payload.update(cpf=cpf(), endereco='Rua de Teste, 123')
    else:
        payload.update(servico='Limpeza pesada', descricao='Teste')
    result = request(c, 'cadastrar_' + role, payload, 201)
    return c, payload, result['id']

admin = client()
request(admin, 'login', dict(tipo='admin', email='zlar2026', senha='747171'))
m, md, mid = register('morador', 'm')
other, od, oid = register('morador', 'other')
p, pd, pid = register('prestador', 'p')
p2, pd2, pid2 = register('prestador', 'p2')
for data, uid in [(pd, pid), (pd2, pid2)]:
    request(admin, 'admin_usuarios', dict(data, action='atualizar', tipo='prestador', id=uid,
        status='ativo', status_aprovacao='aprovado'))

request(admin, 'perfil', dict(action='atualizar', nome='Intruso', telefone=pd['telefone'],
    servico=pd['servico']), 403)
request(admin, 'admin_usuarios', dict(md, action='atualizar', tipo='morador', id=4294967295, status='ativo'), 404)
request(m, 'admin_usuarios', dict(action='listar', tipo='prestador'), 401)
request(client(), 'solicitacoes', dict(action='listar'), 401)
print('OK: papéis, perfil administrativo e usuário inexistente')

for birth in ['', '1990-02-30', 'not-a-date']:
    request(client(), 'cadastrar_prestador', dict(pd, email=f'{RUN}.bad@teste.local', nascimento=birth), 422)
request(client(), 'cadastrar_prestador', dict(pd, nome=[], email=f'{RUN}.bad@teste.local'), 422)
request(m, 'criar_solicitacao', dict(categoria='Limpeza pesada', data='2099-02-30', endereco='Rua', descricao='Teste'), 422)
print('OK: datas impossíveis e entradas inválidas retornam 422')

for raw, expected in [('189.90', '189,90'), ('189,90', '189,90'), ('1.234,56', '1.234,56')]:
    sid = request(m, 'criar_solicitacao', dict(categoria='Limpeza pesada', data='', endereco='Rua', descricao='Regressão monetária'), 201)['id']
    request(p, 'solicitacoes', dict(action='concluir', id=sid), 409)
    request(m, 'avaliacoes', dict(action='criar', id=sid, nota=5), 409)
    request(p, 'solicitacoes', dict(action='aceitar', id=sid))
    request(p2, 'solicitacoes', dict(action='aceitar', id=sid), 409)
    request(p2, 'solicitacoes', dict(action='iniciar', id=sid), 409)
    for action in ['iniciar', 'concluir']:
        request(p, 'solicitacoes', dict(action=action, id=sid))
    for invalid in ['12abc', '-1', '0', '1.234', '1e3', '100000000', []]:
        request(p, 'solicitacoes', dict(action='solicitar_pagamento', id=sid, valor=invalid, pix='teste'), 422)
    request(p, 'solicitacoes', dict(action='solicitar_pagamento', id=sid, valor=raw, pix='teste'))
    rows = request(m, 'solicitacoes', dict(action='listar'))['solicitacoes']
    assert next(r for r in rows if r['id'] == sid)['valor'] == expected
    assert sid not in [r['id'] for r in request(other, 'solicitacoes', dict(action='listar'))['solicitacoes']]
    request(other, 'solicitacoes', dict(action='pagar', id=sid), 409)
    request(m, 'solicitacoes', dict(action='pagar', id=sid))
    request(m, 'solicitacoes', dict(action='pagar', id=sid), 409)
    request(other, 'avaliacoes', dict(action='criar', id=sid, nota=5), 409)
    request(m, 'avaliacoes', dict(action='criar', id=sid, nota=5), 201)
    request(m, 'avaliacoes', dict(action='criar', id=sid, nota=5), 409)
print('OK: valores exatos, autorização, transições, pagamento e avaliação únicos')

code = request(client(), 'esqueci_senha', dict(tipo='prestador', email=pd['email']), 201)['codigo']
reset = dict(action='redefinir', tipo='prestador', email=pd['email'], codigo=code,
    senha='NovaSenha456', confirmar_senha='NovaSenha456')
request(client(), 'esqueci_senha', reset)
request(p, 'session', dict(tipo='prestador'), 401)
request(client(), 'esqueci_senha', reset, 422)
request(p, 'login', dict(tipo='prestador', email=pd['email'], senha='Senha123'), 401)
request(p, 'login', dict(tipo='prestador', email=pd['email'], senha='NovaSenha456'))
print('OK: recuperação invalida sessões e código não pode ser reutilizado')
for path in ['/database_zlar.sql', '/docker-compose.yml', '/.git/config']:
    try:
        urllib.request.urlopen(BASE + path)
        raise AssertionError('Arquivo privado exposto: ' + path)
    except urllib.error.HTTPError as exc:
        assert exc.code in [403, 404]
health = json.load(urllib.request.urlopen(BASE + '/api/check_db.php'))
assert set(health) == {'ok', 'message'}
print('OK: arquivos internos e detalhes do banco não são expostos')
print('REGRESSÕES APROVADAS')
