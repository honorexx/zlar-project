# Zlar - software

Plataforma web para conectar moradores, prestadores de servico e administradores. O sistema permite cadastro, login, solicitacao de servicos, acompanhamento de chamados, suporte, avaliacoes e gerenciamento administrativo.

## Funcionalidades

- Cadastro de moradores e prestadores.
- Login com autenticacao por sessao.
- Protecao de paginas que exigem usuario autenticado.
- Logout com finalizacao da sessao.
- Validacao de formularios com campos obrigatorios, mascaras, RegEx, dicas de preenchimento e confirmacao de senha.
- Bloqueio de cadastros duplicados para campos unicos, como e-mail e CPF.
- Solicitacao de servicos pelo morador.
- Visualizacao e aceite de chamados pelo prestador.
- Area administrativa para acompanhamento de usuarios, prestadores, equipe e solicitacoes.
- Chamados de suporte.
- Integracao entre front-end, PHP e MySQL.

## Tecnologias

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL

## Estrutura do projeto

```text
zlar site/
├── admin/              # Telas administrativas
├── api/                # Endpoints PHP e conexao com banco
├── assets/
│   ├── css/            # Estilos globais
│   ├── img/            # Imagens e comprovacoes
│   └── js/             # Scripts do front-end
├── includes/           # Layout compartilhado e protecao de paginas PHP
├── morador/            # Telas do perfil morador
├── prestador/          # Telas do perfil prestador
├── database_zlar.sql   # Script do banco de dados
└── index.php           # Redirecionamento inicial
```

## Como rodar localmente

1. Instale um ambiente com Apache, PHP e MySQL, como XAMPP.
2. Coloque a pasta do projeto dentro do diretorio do servidor local.
3. Crie o banco de dados importando o arquivo:

```text
database_zlar.sql
```

4. Confira os dados de conexao em:

```text
api/db.php
```

5. Acesse o projeto pelo navegador usando o endereco do servidor local.

## Banco de dados

O banco principal se chama `zlar`. O arquivo `database_zlar.sql` cria as tabelas necessarias para usuarios, moradores, prestadores, solicitacoes, pagamentos, avaliacoes, suporte e acessos administrativos.

Campos unicos importantes:

- `usuarios.email`
- `moradores.cpf`
- `equipe_suporte.email`
- `admin_acessos.usuario`

## Deploy

O projeto precisa de hospedagem com suporte a PHP e MySQL. Ele pode ser publicado em plataformas como Railway, desde que o banco MySQL seja configurado e o arquivo `database_zlar.sql` seja importado.

O GitHub Pages nao executa PHP nem MySQL, entao ele nao roda o sistema completo.

## Observacao

As paginas `.html` usam JavaScript para chamar as APIs em PHP. As paginas `.php` usam o layout compartilhado de `includes/layout.php`, que tambem protege rotas autenticadas.
