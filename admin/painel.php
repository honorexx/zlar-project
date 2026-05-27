<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Painel administrativo', 'admin', 'painel'); ?>
<main class="page-wrap">
  <div class="dashboard-head">
    <div><div class="page-tag">Administrador</div><h1 class="page-title">Gestao da plataforma</h1><p class="page-subtitle">Controle operacional de usuarios, prestadores, equipe e solicitacoes.</p></div>
    <a class="btn btn-secondary" href="login.php">Sair</a>
  </div>
  <div class="dashboard-grid">
    <div class="metric-card"><span>Moradores</span><strong>124</strong></div>
    <div class="metric-card"><span>Prestadores</span><strong>38</strong></div>
    <div class="metric-card"><span>Equipe suporte</span><strong>8</strong></div>
  </div>
  <div class="module-grid">
    <a class="module-card" href="equipe-lista.php"><strong>Equipe de suporte</strong><span>Cadastrar, listar e editar membros internos.</span></a>
    <a class="module-card" href="prestadores-lista.php"><strong>Prestadores</strong><span>Gerenciar profissionais cadastrados.</span></a>
    <a class="module-card" href="moradores-lista.php"><strong>Moradores</strong><span>Consultar usuarios moradores.</span></a>
    <a class="module-card" href="solicitacoes.php"><strong>Solicitacoes</strong><span>Acompanhar pedidos abertos.</span></a>
  </div>
</main>
<?php zlar_footer(); ?>
