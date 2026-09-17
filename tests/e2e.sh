#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8080}"
RUN_ID="$(date +%s)-$$"
TEST_CPF="$(python3 -c 'import secrets; n=[secrets.randbelow(10) for _ in range(9)]; n.append((sum(a*b for a,b in zip(n,range(10,1,-1)))*10%11)%10); n.append((sum(a*b for a,b in zip(n,range(11,1,-1)))*10%11)%10); s="".join(map(str,n)); print(f"{s[:3]}.{s[3:6]}.{s[6:9]}-{s[9:]}")')"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT
MORADOR_COOKIE="$TMP_DIR/morador.cookie"
PRESTADOR_COOKIE="$TMP_DIR/prestador.cookie"
ADMIN_COOKIE="$TMP_DIR/admin.cookie"
ADMIN_RECOVERY_COOKIE="$TMP_DIR/admin-recovery.cookie"

request() {
  local cookie="$1" endpoint="$2" payload="$3"
  curl -sS -c "$cookie" -b "$cookie" -H 'Content-Type: application/json' -X POST "$BASE_URL/api/$endpoint" --data "$payload"
}

assert_ok() {
  local json="$1" label="$2"
  if [[ "$(jq -r '.ok // false' <<<"$json")" != "true" ]]; then
    echo "FALHOU: $label" >&2
    jq . <<<"$json" >&2
    exit 1
  fi
  echo "OK: $label"
}

assert_error() {
  local json="$1" label="$2"
  if ! jq -e '.ok == false' <<<"$json" >/dev/null; then
    echo "FALHOU: $label deveria ser negado" >&2
    jq . <<<"$json" >&2
    exit 1
  fi
  echo "OK: $label"
}

M_EMAIL="morador.${RUN_ID}@teste.local"
P_EMAIL="prestador.${RUN_ID}@teste.local"

json="$(request "$MORADOR_COOKIE" cadastrar_morador.php "$(jq -nc --arg e "$M_EMAIL" --arg cpf "$TEST_CPF" '{nome:"Morador Teste",email:$e,telefone:"(41) 99999-1000",cpf:$cpf,nascimento:"1990-01-01",endereco:"Rua do Teste, 100",senha:"Senha123",confirmar_senha:"Senha123"}')")"
assert_ok "$json" 'cadastro do morador e sessão própria'

json="$(request "$PRESTADOR_COOKIE" cadastrar_prestador.php "$(jq -nc --arg e "$P_EMAIL" '{nome:"Prestador Teste",email:$e,telefone:"(41) 99999-2000",nascimento:"1988-02-02",servico:"Limpeza pesada",descricao:"Profissional de teste",senha:"Senha123",confirmar_senha:"Senha123"}')")"
assert_ok "$json" 'cadastro do prestador pendente e sessão própria'

json="$(request "$PRESTADOR_COOKIE" solicitacoes.php '{"action":"listar"}')"
assert_error "$json" 'prestador pendente não visualiza solicitações'

json="$(request "$ADMIN_COOKIE" login.php '{"tipo":"admin","email":"zlar2026","senha":"747171"}')"
assert_ok "$json" 'login do administrador em sessão separada'

json="$(request "$ADMIN_COOKIE" admin_usuarios.php '{"action":"listar","tipo":"prestador"}')"
assert_ok "$json" 'admin lista prestadores pelo MySQL'
P_ID="$(jq -r --arg e "$P_EMAIL" '.usuarios[] | select(.email==$e) | .id' <<<"$json")"
json="$(request "$ADMIN_COOKIE" admin_usuarios.php "$(jq -nc --argjson id "$P_ID" --arg e "$P_EMAIL" '{action:"atualizar",tipo:"prestador",id:$id,nome:"Prestador Teste",email:$e,telefone:"(41) 99999-2000",status:"ativo",servico:"Limpeza pesada",descricao:"Profissional aprovado",status_aprovacao:"aprovado"}')")"
assert_ok "$json" 'admin aprova prestador'

json="$(request "$MORADOR_COOKIE" criar_solicitacao.php '{"categoria":"Limpeza pesada","data":"","endereco":"Rua do Teste, 100","descricao":"Limpeza completa para o teste integrado"}')"
assert_ok "$json" 'morador cria solicitação persistida'
S_ID="$(jq -r '.id' <<<"$json")"

json="$(request "$PRESTADOR_COOKIE" solicitacoes.php '{"action":"listar"}')"
assert_ok "$json" 'prestador aprovado visualiza solicitação'
[[ "$(jq -r --argjson id "$S_ID" '.solicitacoes[] | select(.id==$id) | .status' <<<"$json")" == 'aberta' ]]

for action in aceitar iniciar concluir; do
  json="$(request "$PRESTADOR_COOKIE" solicitacoes.php "$(jq -nc --arg a "$action" --argjson id "$S_ID" '{action:$a,id:$id}')")"
  assert_ok "$json" "prestador executa transição: $action"
done

json="$(request "$PRESTADOR_COOKIE" solicitacoes.php "$(jq -nc --argjson id "$S_ID" '{action:"solicitar_pagamento",id:$id,valor:"189,90",pix:"prestador@pix.local",observacao:"Serviço concluído"}')")"
assert_ok "$json" 'prestador solicita pagamento'

json="$(request "$MORADOR_COOKIE" solicitacoes.php "$(jq -nc --argjson id "$S_ID" '{action:"pagar",id:$id}')")"
assert_ok "$json" 'morador paga solicitação'
json="$(request "$MORADOR_COOKIE" avaliacoes.php "$(jq -nc --argjson id "$S_ID" '{action:"criar",id:$id,nota:5,comentario:"Excelente atendimento"}')")"
assert_ok "$json" 'morador avalia serviço pago'
json="$(request "$PRESTADOR_COOKIE" avaliacoes.php '{"action":"listar"}')"
assert_ok "$json" 'avaliação aparece para o prestador'
[[ "$(jq -r --argjson id "$S_ID" '.avaliacoes[] | select(.solicitacaoId==$id) | .nota' <<<"$json")" == '5' ]]

json="$(request "$MORADOR_COOKIE" perfil.php "$(jq -nc --arg e "$M_EMAIL" --arg cpf "$TEST_CPF" '{action:"atualizar",nome:"Morador Atualizado",email:$e,telefone:"(41) 98888-1000",endereco:"Rua Atualizada, 200"}')")"
assert_ok "$json" 'morador edita perfil no MySQL'
json="$(request "$PRESTADOR_COOKIE" perfil.php '{"action":"atualizar","nome":"Prestador Atualizado","telefone":"(41) 98888-2000","servico":"Limpeza pesada","descricao":"Perfil atualizado"}')"
assert_ok "$json" 'prestador edita perfil no MySQL'

json="$(request "$MORADOR_COOKIE" chamados_suporte.php '{"action":"criar","tipo":"Pagamento ou Pix","urgencia":"Alta","descricao":"Chamado de integração"}')"
assert_ok "$json" 'morador abre suporte'
C_ID="$(jq -r '.id' <<<"$json")"
json="$(request "$ADMIN_COOKIE" chamados_suporte.php "$(jq -nc --argjson id "$C_ID" '{action:"atualizar",id:$id,status:"resolvido",urgencia:"Alta",tipo:"Pagamento ou Pix",descricao:"Chamado de integração",resposta:"Resolvido pelo administrador"}')")"
assert_ok "$json" 'administrador responde suporte'
json="$(request "$MORADOR_COOKIE" chamados_suporte.php '{"action":"listar"}')"
assert_ok "$json" 'resposta de suporte sincroniza com morador'
[[ "$(jq -r --argjson id "$C_ID" '.chamados[] | select(.id==$id) | .resposta' <<<"$json")" == 'Resolvido pelo administrador' ]]

json="$(request "$PRESTADOR_COOKIE" esqueci_senha.php "$(jq -nc --arg e "$P_EMAIL" '{action:"gerar",tipo:"prestador",email:$e}')")"
assert_ok "$json" 'gera recuperação de senha'
CODE="$(jq -r '.codigo' <<<"$json")"
json="$(request "$PRESTADOR_COOKIE" esqueci_senha.php "$(jq -nc --arg e "$P_EMAIL" --arg c "$CODE" '{action:"redefinir",tipo:"prestador",email:$e,codigo:$c,senha:"NovaSenha456",confirmar_senha:"NovaSenha456"}')")"
assert_ok "$json" 'redefine senha com código de uso único'

json="$(request "$ADMIN_RECOVERY_COOKIE" esqueci_senha.php '{"action":"gerar","tipo":"admin","email":"zlar2026"}')"
assert_ok "$json" 'gera recuperação do código administrativo'
ADMIN_CODE="$(jq -r '.codigo' <<<"$json")"
json="$(request "$ADMIN_RECOVERY_COOKIE" esqueci_senha.php "$(jq -nc --arg c "$ADMIN_CODE" '{action:"redefinir",tipo:"admin",email:"zlar2026",codigo:$c,senha:"Admin789",confirmar_senha:"Admin789"}')")"
assert_ok "$json" 'redefine código administrativo'
json="$(request "$ADMIN_RECOVERY_COOKIE" login.php '{"tipo":"admin","email":"zlar2026","senha":"Admin789"}')"
assert_ok "$json" 'login administrativo usa o novo código'
json="$(request "$ADMIN_RECOVERY_COOKIE" esqueci_senha.php '{"action":"gerar","tipo":"admin","email":"zlar2026"}')"
ADMIN_CODE="$(jq -r '.codigo' <<<"$json")"
json="$(request "$ADMIN_RECOVERY_COOKIE" esqueci_senha.php "$(jq -nc --arg c "$ADMIN_CODE" '{action:"redefinir",tipo:"admin",email:"zlar2026",codigo:$c,senha:"747171",confirmar_senha:"747171"}')")"
assert_ok "$json" 'restaura o código administrativo local documentado'
json="$(request "$ADMIN_COOKIE" login.php '{"tipo":"admin","email":"zlar2026","senha":"747171"}')"
assert_ok "$json" 'admin autentica novamente após redefinição'
json="$(request "$PRESTADOR_COOKIE" login.php "$(jq -nc --arg e "$P_EMAIL" '{tipo:"prestador",email:$e,senha:"NovaSenha456"}')")"
assert_ok "$json" 'prestador autentica novamente após redefinição'

json="$(request "$ADMIN_COOKIE" admin_usuarios.php "$(jq -nc --argjson id "$P_ID" --arg e "$P_EMAIL" '{action:"atualizar",tipo:"prestador",id:$id,nome:"Prestador Atualizado",email:$e,telefone:"(41) 98888-2000",status:"bloqueado",servico:"Limpeza pesada",descricao:"Perfil atualizado",status_aprovacao:"aprovado"}')")"
assert_ok "$json" 'administrador bloqueia prestador'
json="$(request "$PRESTADOR_COOKIE" session.php '{"tipo":"prestador"}')"
assert_error "$json" 'bloqueio invalida sessão existente do prestador'

json="$(request "$ADMIN_COOKIE" admin_usuarios.php '{"action":"listar","tipo":"morador"}')"
M_ID="$(jq -r --arg e "$M_EMAIL" --arg cpf "$TEST_CPF" '.usuarios[] | select(.email==$e) | .id' <<<"$json")"
json="$(request "$ADMIN_COOKIE" admin_usuarios.php "$(jq -nc --argjson id "$M_ID" --arg e "$M_EMAIL" --arg cpf "$TEST_CPF" '{action:"atualizar",tipo:"morador",id:$id,nome:"Morador Atualizado",email:$e,telefone:"(41) 98888-1000",status:"bloqueado",cpf:$cpf,endereco:"Rua Atualizada, 200"}')")"
assert_ok "$json" 'administrador bloqueia morador'
json="$(request "$MORADOR_COOKIE" session.php '{"tipo":"morador"}')"
assert_error "$json" 'bloqueio invalida sessão existente do morador'

echo 'FLUXO COMPLETO APROVADO EM SESSÕES SEPARADAS: MORADOR, PRESTADOR E ADMINISTRADOR'
