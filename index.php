<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zlar | Inicio</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="icon" type="image/jpeg" href="assets/img/favicon-zlar.jpeg">
</head>
<body>
  <header class="zlar-header">
    <a class="zlar-logo" href="index.php">
      <span class="brand-lockup">
        <img class="zlar-symbol-img" src="assets/img/zlar-logo-nova.jpeg" alt="Zlar">
        <span class="brand-text"><span>ZLAR</span><small>Inicio</small></span>
      </span>
    </a>
    <nav class="header-nav">
      <a href="morador/login.html">Morador</a>
      <a href="prestador/login.html">Prestador</a>
      <a href="admin/login.html">Admin</a>
    </nav>
  </header>

  <main class="page-wrap auth-wrap">
    <section class="card auth-card">
      <div class="page-tag">Projeto Zlar</div>
      <h1 class="page-title">Pagina inicial</h1>
      <p class="page-subtitle">Escolha uma area para acessar o sistema no XAMPP.</p>

      <div class="actions-row">
        <a class="btn btn-primary" href="morador/login.html">Morador</a>
        <a class="btn btn-secondary" href="prestador/login.html">Prestador</a>
      </div>
      <div class="actions-row">
        <a class="btn btn-ghost" href="admin/login.html">Admin</a>
        <a class="btn btn-ghost" href="api/check_db.php">Testar banco</a>
      </div>
    </section>
  </main>
</body>
</html>
