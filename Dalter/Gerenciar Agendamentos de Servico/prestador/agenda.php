<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Agenda do prestador', 'prestador', 'agenda'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Prestador</div>
    <h1 class="page-title">Agenda de atendimentos</h1>
    <p class="page-subtitle">Organizacao dos servicos aceitos.</p>
  </div>
  <div class="module-grid">
    <section class="card"><div class="section-label">Hoje</div><h3>14:00 - Reparo eletrico</h3><p class="page-subtitle">Centro, Curitiba</p></section>
    <section class="card"><div class="section-label">Amanha</div><h3>09:30 - Manutencao</h3><p class="page-subtitle">Batel, Curitiba</p></section>
  </div>
</main>
<?php zlar_footer(); ?>
