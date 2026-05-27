<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Avaliacoes do morador', 'morador', 'avaliacoes'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Morador</div>
    <h1 class="page-title">Minhas avaliacoes</h1>
    <p class="page-subtitle">Avalie atendimentos concluidos e acompanhe feedbacks enviados.</p>
  </div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Servico</th><th>Prestador</th><th>Nota</th><th>Descricao</th><th>Status</th></tr></thead>
      <tbody id="tbAvaliacoesMorador">
        <tr><td colspan="5"><div class="empty-state"><h3>Nenhuma avaliacao enviada</h3><p>Apos pagar um servico, voce pode escolher se deseja avaliar o prestador.</p></div></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
