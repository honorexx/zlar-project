# Zlar

Plataforma web para conectar moradores e prestadores de serviços domésticos. O projeto usa PHP no backend, HTML/CSS/JavaScript na interface e MySQL como única fonte persistente de dados.

## Funcionalidades

- Cadastro, login, sessão e recuperação de senha de moradores e prestadores.
- Cadastro, login e recuperação do código de acesso administrativo.
- Aprovação, reprovação e bloqueio de prestadores pelo administrador.
- Ativação, inativação e bloqueio de moradores e prestadores.
- Edição de perfil persistida no MySQL.
- Fluxo completo da solicitação: criação, aceite, início, conclusão, solicitação de pagamento, pagamento e avaliação.
- Avaliações vinculadas a uma solicitação paga, com média recalculada no perfil do prestador.
- Chamados de suporte sincronizados entre usuário e administrador.
- Validações no navegador e, obrigatoriamente, no backend.
- Controle de autorização por papel e validação da conta a cada requisição.

## Arquitetura

```text
admin/                 telas administrativas
morador/               telas do morador
prestador/             telas do prestador
assets/                 estilos, imagens e JavaScript da interface
api/                    endpoints PHP e regras de negócio
database_zlar.sql       esquema MySQL e dados locais iniciais
tests/e2e.sh            teste integrado com três sessões independentes
Dockerfile              servidor PHP/Apache
docker-compose.yml      PHP/Apache + MySQL
```

O navegador não guarda solicitações, pagamentos, avaliações, perfis ou chamados como fonte de dados. Esses dados são sempre consultados e alterados pelas APIs, que usam o MySQL.

## Requisitos

- Docker Desktop ou outro daemon compatível com Docker Compose.
- `curl` e `jq` para executar o teste integrado.

Também é possível usar Apache/PHP 8.2+ e MySQL 8 instalados diretamente, desde que a extensão `pdo_mysql` esteja habilitada e as variáveis de banco sejam configuradas.

## Executar com Docker

Na pasta do projeto:

```bash
docker compose up --build -d
```

Em instalações que disponibilizam o Compose como comando separado, use `docker-compose up --build -d`.

Aguarde o MySQL ficar saudável e acesse:

```text
http://localhost:8080/
```

Credencial administrativa inicial, somente para o ambiente local:

```text
Usuário: zlar2026
Código: 747171
```

Troque essa credencial antes de disponibilizar o sistema publicamente.

### Recriar o banco local

O esquema é importado automaticamente apenas quando o volume MySQL é criado. Para apagar os dados locais e importar novamente:

```bash
docker compose down -v
docker compose up --build -d
```

Esse comando remove definitivamente o banco local do projeto.

## Configuração sem Docker

Importe `database_zlar.sql` em um banco MySQL e configure:

```text
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_DATABASE=zlar
MYSQL_USER=zlar
MYSQL_PASSWORD=sua_senha
APP_ENV=production
```

As mesmas informações podem ser fornecidas em `DATABASE_URL` ou `MYSQL_URL`.

Em `APP_ENV=local`, o código temporário de recuperação é mostrado na própria tela para permitir testes. Em produção ele não é devolvido pela API; a implantação deve integrar o envio do código por um provedor de e-mail.

## Regras do fluxo

1. O morador cria a conta e abre uma solicitação.
2. O administrador aprova o prestador.
3. Apenas prestadores ativos, aprovados e da mesma categoria visualizam a solicitação aberta.
4. Um único prestador pode aceitar a solicitação.
5. O prestador inicia e conclui o serviço.
6. O prestador informa valor e chave Pix e solicita o pagamento.
7. Apenas o morador dono da solicitação pode registrar o pagamento.
8. Apenas depois do pagamento o morador pode avaliar, uma única vez.
9. A avaliação aparece para o prestador e atualiza sua média no MySQL.

As transições são validadas no banco pelo backend. Repetições, mudanças fora de ordem e tentativas feitas pelo usuário errado são recusadas.

## Teste integrado

Com os contêineres ativos:

```bash
./tests/e2e.sh
```

O teste cria dados únicos e usa três arquivos de cookie independentes para representar Morador, Prestador e Administrador. Ele verifica cadastro, aprovação, restrição do prestador pendente, fluxo completo do serviço e pagamento, avaliação, perfis, suporte sincronizado, recuperação de senha, bloqueios e invalidação de sessões.

## Segurança e produção

- Cookies de sessão são `HttpOnly` e `SameSite=Lax`; em HTTPS também recebem `Secure`.
- O identificador da sessão é renovado no login.
- Senhas de moradores e prestadores usam `password_hash`/`password_verify`.
- Códigos temporários de recuperação são armazenados como hash, expiram e têm uso único.
- O código administrativo inicial é apenas uma conveniência local. Use uma credencial forte e HTTPS em produção.
- O botão de pagamento registra a confirmação no sistema; ele não movimenta dinheiro por um gateway externo.
- O envio de e-mail de recuperação deve ser conectado antes da publicação em produção.

## Parar o ambiente

```bash
docker compose down
```

Sem a opção `-v`, os dados permanecem no volume MySQL para a próxima execução.
