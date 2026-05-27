<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Financeiro do prestador', 'prestador', 'financeiro'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Prestador</div>
    <h1 class="page-title">Financeiro</h1>
    <p class="page-subtitle">Resumo de recebimentos e repasses.</p>
  </div>
  <div class="dashboard-grid">
    <div class="metric-card"><span>Saldo a receber</span><strong>R$ 640</strong></div>
    <div class="metric-card"><span>Servicos pagos</span><strong>12</strong></div>
    <div class="metric-card"><span>Proximo repasse</span><strong>25/05</strong></div>
  </div>
</main>
<?php zlar_footer(); ?>
