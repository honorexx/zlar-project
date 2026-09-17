# Zlar

Plataforma web para conectar moradores e prestadores de serviços domésticos. O projeto usa PHP no backend, HTML/CSS/JavaScript na interface e MySQL como única fonte persistente de dados.

## Funcionalidades

- Cadastro, login, sessão e recuperação de senha de moradores e prestadores.
- Login administrativo e recuperação local do código de acesso; não há cadastro público de administradores.
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
- `curl`, `jq` e Python 3 para executar os testes.

Também é possível usar Apache/PHP 8.2+ e MySQL 8 instalados diretamente, desde que as extensões `pdo_mysql` e `mbstring` estejam habilitadas e as variáveis de banco sejam configuradas.

## Executar com Docker

Na pasta do projeto:

```bash
docker compose up --build -d
```

Em instalações que disponibilizam o Compose como comando separado, use `docker-compose up --build -d`.

O Compose publica a aplicação somente neste computador (`127.0.0.1`). Para outra porta, execute `ZLAR_PORT=8088 docker compose up --build -d`.

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

## Banco existente e migração

`database_zlar.sql` é um inicializador destrutivo: contém `DROP TABLE`. Não o importe sobre dados que deseja manter. Ele não constitui uma migração automática de versões antigas. Faça backup e prepare uma migração específica para preservar um banco existente. Os testes devem usar um banco local descartável; criam cadastros e alteram temporariamente a credencial administrativa de demonstração.

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

A recuperação funciona apenas quando `APP_ENV=local` está explicitamente configurado: o código temporário aparece na tela para testes. Em qualquer outro ambiente, inclusive quando a variável não está definida, a API retorna HTTP 503 e informa que o envio de e-mail ainda não está configurado. Antes de habilitar recuperação em produção, é necessário integrar a entrega por e-mail e limitar tentativas. A redefinição invalida as sessões abertas anteriormente.

Ao usar Apache diretamente, impeça acesso HTTP a `database_zlar.sql`, arquivos de configuração, testes e `.git`. A imagem Docker já copia somente as pastas e páginas necessárias para servir a aplicação.

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
python3 tests/regression.py
```

Para um ambiente isolado, sem usar o volume padrão:

```bash
ZLAR_PORT=8088 docker compose -p zlar-review up --build -d
BASE_URL=http://localhost:8088 ./tests/e2e.sh
BASE_URL=http://localhost:8088 python3 tests/regression.py
```

O teste cria dados únicos e usa três arquivos de cookie independentes para representar Morador, Prestador e Administrador. Ele verifica cadastro, aprovação, restrição do prestador pendente, fluxo completo do serviço e pagamento, avaliação, perfis, suporte sincronizado, recuperação de senha, bloqueios e invalidação de sessões.

A suíte de regressão verifica valores monetários com ponto e vírgula, datas inválidas, autorização entre contas, transições fora de ordem, pagamento e avaliação duplicados, invalidação de sessão após redefinição e proteção de arquivos internos na imagem Docker. O fluxo integrado usa CPFs de teste distintos para permitir execuções repetidas.

O GitHub Actions executa os testes com um MySQL novo a cada execução.

## Segurança e produção

- Cookies de sessão são `HttpOnly` e `SameSite=Lax`; em HTTPS também recebem `Secure`.
- O identificador da sessão é renovado no login.
- Senhas de moradores e prestadores usam `password_hash`/`password_verify`.
- Códigos temporários de recuperação são armazenados como hash, expiram e têm uso único.
- Valores aceitos no pagamento: `189,90`, `189.90` ou `1.234,56`; entradas ambíguas e inválidas são recusadas.
- O código administrativo inicial é apenas uma conveniência local. Use uma credencial forte e HTTPS em produção.
- O botão de pagamento registra a confirmação no sistema; ele não movimenta dinheiro por um gateway externo.
- A recuperação por e-mail, proteção contra tentativas repetidas e integração financeira real ainda não estão implementadas. O projeto é um protótipo local; os testes não equivalem a uma certificação para produção.

## Parar o ambiente

```bash
docker compose down
```

Sem a opção `-v`, os dados permanecem no volume MySQL para a próxima execução.
