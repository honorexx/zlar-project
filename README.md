# Zlar Web em Java

Projeto web simples feito com Java puro.

Ele abre no navegador, mas nao usa JavaScript, PHP, XAMPP, Maven, Spring ou banco de dados.

Os dados ficam na memoria enquanto o programa esta aberto. Quando fechar e abrir de novo, os cadastros voltam ao zero. Isso deixa o projeto facil de explicar.

## Como rodar

Abra o terminal nesta pasta e execute:

```text
compilar.bat
rodar.bat
```

Depois acesse:

```text
http://localhost:8090
```

Se a porta 8090 ja estiver ocupada, rode assim:

```text
java -DPORT=8091 -cp out\production\zlar_oficial ZlarApp
```

## Login do administrador

```text
Usuario: zlar2026
Codigo: 747171
```

## Arquivos principais

```text
src/ZlarApp.java          Entrada do programa
src/ServidorWeb.java      Rotas web do sistema
src/Pagina.java           Carrega os templates HTML
src/BancoDados.java       Listas que guardam os dados
src/Usuario.java          Classe de usuario
src/Solicitacao.java      Classe de solicitacao
src/Chamado.java          Classe de chamado de suporte
templates/                Paginas HTML separadas do Java
web/estilo.css            Aparencia do sistema
compilar.bat              Compila o projeto
rodar.bat                 Inicia o servidor web
```

## O que o sistema faz

- Cadastro de morador
- Cadastro de prestador
- Login de morador, prestador e administrador
- Morador cria solicitacao de servico
- Prestador aceita e conclui solicitacoes
- Morador ve qual prestador aceitou
- Morador paga a solicitacao depois que ela for concluida
- Usuario abre chamado de suporte
- Administrador ve usuarios e responde chamados

## Fluxo da solicitacao

1. Morador abre uma solicitacao.
2. Prestador ve a solicitacao do servico dele.
3. Prestador clica em `Aceitar`.
4. Morador ve o nome do prestador no painel.
5. Prestador clica em `Concluir`.
6. Morador clica em `Pagar`.

## Como explicar para leigos

O projeto foi separado em partes:

1. `ZlarApp`: liga o sistema.
2. `ServidorWeb`: recebe os acessos do navegador.
3. `Pagina`: abre os arquivos da pasta `templates`.
4. `BancoDados`: guarda os dados em listas.
5. `Usuario`, `Solicitacao` e `Chamado`: representam as informacoes.

Quando alguem clica em um botao, o navegador envia um formulario para o Java. O Java executa a regra e devolve uma pagina pronta.
