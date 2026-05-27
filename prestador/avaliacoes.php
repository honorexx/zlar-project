<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Avaliacoes do prestador', 'prestador', 'avaliacoes'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Prestador</div>
    <h1 class="page-title">Avaliacoes recebidas</h1>
    <p class="page-subtitle">Feedbacks enviados pelos moradores atendidos.</p>
  </div>
  <section class="rating-summary">
    <div class="metric-card"><span>Nota media</span><strong id="prestador-nota-media">-</strong></div>
    <div class="metric-card"><span>Total de avaliacoes</span><strong id="prestador-total-avaliacoes">0</strong></div>
    <div class="metric-card"><span>Distribuicao</span><div class="rating-distribution" id="prestador-distribuicao-avaliacoes"><span>5 estrelas: <strong>0</strong></span><span>4 estrelas: <strong>0</strong></span><span>3 estrelas: <strong>0</strong></span><span>2 estrelas: <strong>0</strong></span><span>1 estrela: <strong>0</strong></span></div></div>
  </section>
  <section class="card table-card">
    <table>
      <thead><tr><th>Morador</th><th>Servico</th><th>Nota</th><th>Descricao</th><th>Data</th></tr></thead>
      <tbody id="tbAvaliacoesPrestador">
        <tr><td colspan="5"><div class="empty-state"><h3>Nenhuma avaliacao recebida</h3><p>Quando um morador avaliar depois do pagamento, o feedback aparece aqui.</p></div></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
