<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function zlar_base_path() {
  return $GLOBALS['basePath'] ?? '..';
}

function zlar_header($title, $area, $active = '') {
  $base = zlar_base_path();
  $publicPages = ['login', 'cadastro', 'esqueci-senha'];
  $user = $_SESSION['zlar_user'] ?? null;
  if (!in_array($active, $publicPages, true) && (!$user || ($user['tipo'] ?? '') !== $area)) {
    header("Location: $base/$area/login.html");
    exit;
  }
  $navs = [
    'morador' => [
      ['login', 'Login', "$base/morador/login.php"],
      ['cadastro', 'Cadastro', "$base/morador/cadastro.php"],
      ['painel', 'Painel', "$base/morador/painel.php"],
      ['solicitar', 'Solicitar servico', "$base/morador/solicitar.php"],
      ['historico', 'Historico', "$base/morador/historico.php"],
      ['pagamentos', 'Pagamentos', "$base/morador/pagamentos.php"],
      ['avaliacoes', 'Avaliacoes', "$base/morador/avaliacoes.php"],
      ['perfil', 'Perfil', "$base/morador/perfil.php"],
    ],
    'prestador' => [
      ['login', 'Login', "$base/prestador/login.php"],
      ['cadastro', 'Cadastro', "$base/prestador/cadastro.php"],
      ['painel', 'Painel', "$base/prestador/painel.php"],
      ['chamados', 'Chamados', "$base/prestador/chamados.php"],
      ['agenda', 'Agenda', "$base/prestador/agenda.php"],
      ['financeiro', 'Financeiro', "$base/prestador/financeiro.php"],
      ['avaliacoes', 'Avaliacoes', "$base/prestador/avaliacoes.php"],
      ['perfil', 'Perfil', "$base/prestador/perfil.php"],
    ],
    'admin' => [
      ['login', 'Login', "$base/admin/login.php"],
      ['painel', 'Painel', "$base/admin/painel.php"],
      ['equipe', 'Equipe', "$base/admin/equipe-lista.php"],
      ['prestadores', 'Prestadores', "$base/admin/prestadores-lista.php"],
      ['moradores', 'Moradores', "$base/admin/moradores-lista.php"],
      ['solicitacoes', 'Solicitacoes', "$base/admin/solicitacoes.php"],
      ['relatorios', 'Relatorios', "$base/admin/relatorios.php"],
      ['configuracoes', 'Configuracoes', "$base/admin/configuracoes.php"],
    ],
  ];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?> | Zlar</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css">
</head>
<body>
  <header class="zlar-header">
    <a class="zlar-logo" href="<?php echo $base; ?>/morador/login.html" aria-label="Zlar">
      <span class="brand-lockup">
        <img class="zlar-symbol-img" src="<?php echo $base; ?>/assets/img/zlar-logo-nova.jpeg" alt="Zlar">
        <span class="brand-text"><span>ZLAR</span><small><?php echo htmlspecialchars($area); ?></small></span>
      </span>
    </a>
    <nav class="header-nav">
      <?php foreach (($navs[$active ? $area : $area] ?? []) as $item): ?>
        <a class="<?php echo $active === $item[0] ? 'active' : ''; ?>" href="<?php echo $item[2]; ?>"><?php echo $item[1]; ?></a>
      <?php endforeach; ?>
      <?php if ($user): ?>
        <span class="user-chip"><?php echo htmlspecialchars(($user['nome'] ?? 'Usuario') . ' - ' . ucfirst($user['tipo'] ?? $area)); ?></span>
        <a href="<?php echo $base; ?>/api/logout_page.php?area=<?php echo urlencode($area); ?>">Sair</a>
      <?php endif; ?>
    </nav>
  </header>
<?php
}

function zlar_footer() {
  $base = zlar_base_path();
?>
  <div class="toast" id="toast"></div>
  <script src="<?php echo $base; ?>/assets/js/script.js"></script>
</body>
</html>
<?php
}
?>
