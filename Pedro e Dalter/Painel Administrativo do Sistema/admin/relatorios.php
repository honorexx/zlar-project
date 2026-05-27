<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Relatorios administrativos', 'admin', 'relatorios'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Admin</div>
    <h1 class="page-title">Relatorios</h1>
    <p class="page-subtitle">Indicadores de operacao, cadastros e atendimentos.</p>
  </div>
  <div class="dashboard-grid">
    <div class="metric-card"><span>Solicitacoes no mes</span><strong>86</strong></div>
    <div class="metric-card"><span>Taxa de conclusao</span><strong>74%</strong></div>
    <div class="metric-card"><span>Novos cadastros</span><strong>29</strong></div>
  </div>
  <section class="card">
    <div class="section-label">Resumo</div>
    <p class="page-subtitle">Esta tela esta preparada para receber graficos e filtros quando o banco de dados for integrado.</p>
  </section>
</main>
<?php zlar_footer(); ?>
