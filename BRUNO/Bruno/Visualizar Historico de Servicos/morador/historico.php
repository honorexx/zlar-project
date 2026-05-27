<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Historico do morador', 'morador', 'historico'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Morador</div>
    <h1 class="page-title">Historico de solicitacoes</h1>
    <p class="page-subtitle">Lista de pedidos feitos pelo morador.</p>
  </div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Servico</th><th>Data</th><th>Status</th><th>Prestador</th><th>Informacoes do prestador</th><th>Acao</th></tr></thead>
      <tbody id="historicoMorador">
        <tr><td colspan="6"><div class="empty-state"><h3>Nenhuma solicitacao encontrada</h3><p>Quando voce solicitar um servico, ele aparece aqui.</p></div></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
