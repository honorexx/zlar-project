<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Solicitacoes', 'admin', 'solicitacoes'); ?>
<main class="page-wrap">
  <div class="page-title-block"><div class="page-tag">Admin</div><h1 class="page-title">Solicitacoes da plataforma</h1><p class="page-subtitle">Pedidos feitos por moradores e seus status.</p></div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Morador</th><th>Servico</th><th>Prestador</th><th>Status</th><th>Data</th></tr></thead>
      <tbody>
        <tr><td>Mariana Alves</td><td>Eletricista</td><td>A definir</td><td><span class="badge badge-warn">Aberta</span></td><td>18/05/2026</td></tr>
        <tr><td>Rafael Costa</td><td>Limpeza</td><td>Bruna Silva</td><td><span class="badge badge-success">Concluida</span></td><td>02/05/2026</td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
