<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Pagamentos do morador', 'morador', 'pagamentos'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Morador</div>
    <h1 class="page-title">Pagamentos</h1>
    <p class="page-subtitle">Metodos de pagamento e cobrancas dos servicos contratados.</p>
  </div>
  <div class="module-grid">
    <section class="card"><div class="section-label">Metodo principal</div><h3>Cartao final 4421</h3><p class="page-subtitle">Credito cadastrado para pagamentos automaticos.</p><button class="btn btn-secondary">Alterar</button></section>
    <section class="card"><div class="section-label">Ultima cobranca</div><h3>R$ 180,00</h3><p class="page-subtitle">Limpeza residencial - 02/05/2026.</p><span class="badge badge-success">Pago</span></section>
  </div>
</main>
<?php zlar_footer(); ?>
