# Zlar

Sistema simples para apresentar no XAMPP. Ele usa telas HTML, JavaScript, APIs PHP e banco MySQL.

## Como rodar no XAMPP

1. Deixe a pasta dentro de:

```text
C:\xampp\htdocs\zlar site
```

2. Abra o XAMPP e ligue:

```text
Apache
MySQL
```

3. Entre no phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Crie um banco chamado:

```text
zlar
```

5. Importe o arquivo:

```text
database_zlar.sql
```

6. Teste a conexao:

```text
http://localhost/zlar%20site/api/check_db.php
```

Se aparecer `"ok": true`, o banco esta funcionando.

7. Abra o projeto:

```text
http://localhost/zlar%20site/
```

## Login de admin para apresentar

```text
Usuario: zlar2026
Codigo: 747171
```

Morador e prestador podem ser criados pelas telas de cadastro.

## Arquivos principais

```text
zlar site/
├── admin/              Telas HTML do administrador
├── api/                PHP que acessa o banco
├── assets/css/         Estilo do site
├── assets/js/          JavaScript das telas
├── assets/img/         Logo e favicon
├── morador/            Telas HTML do morador
├── prestador/          Telas HTML do prestador
├── database_zlar.sql   Banco para importar
└── index.php           Entrada do projeto
```

## Onde alterar

- Aparencia: `assets/css/styles.css`
- Comportamento das telas: `assets/js/script.js`
- Conexao com o banco: `api/config.php`
- Estrutura do banco: `database_zlar.sql`
- Paginas do morador: `morador/*.html`
- Paginas do prestador: `prestador/*.html`
- Paginas do admin: `admin/*.html`

## Observacao importante

Abra sempre pelo XAMPP usando `http://localhost/...`. Se abrir os arquivos direto pelo Windows, o PHP e o banco nao funcionam.
