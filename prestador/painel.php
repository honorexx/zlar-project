<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Painel do prestador', 'prestador', 'painel'); ?>
<main class="page-wrap">
  <div class="dashboard-head">
    <div><div class="page-tag">Painel do prestador</div><h1 class="page-title">Meu trabalho</h1><p class="page-subtitle">Acompanhe chamados e sua disponibilidade.</p></div>
    <a class="btn btn-secondary" href="login.php">Sair</a>
  </div>
  <div class="dashboard-grid">
    <div class="metric-card"><span>Chamados novos</span><strong>2</strong></div>
    <div class="metric-card"><span>Agenda da semana</span><strong>4</strong></div>
    <div class="metric-card"><span>Avaliacao media</span><strong id="prestador-nota-media">-</strong></div>
  </div>
  <div class="module-grid">
    <a class="module-card" href="chamados.php"><strong>Chamados</strong><span>Ver solicitacoes recebidas.</span></a>
    <a class="module-card" href="agenda.php"><strong>Agenda</strong><span>Organizar atendimentos aceitos.</span></a>
    <a class="module-card" href="cadastro.php"><strong>Meu perfil</strong><span>Atualizar dados profissionais.</span></a>
  </div>
</main>
<?php zlar_footer(); ?>
