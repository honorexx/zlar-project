<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Chamados do prestador', 'prestador', 'chamados'); ?>
<main class="page-wrap">
  <div class="page-title-block">
    <div class="page-tag">Prestador</div>
    <h1 class="page-title">Chamados recebidos</h1>
    <p class="page-subtitle">Solicitacoes de moradores disponiveis para atendimento.</p>
  </div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Morador</th><th>Servico</th><th>Bairro</th><th>Status</th><th>Acao</th></tr></thead>
      <tbody>
        <tr><td>Mariana Alves</td><td>Eletricista</td><td>Centro</td><td><span class="badge badge-warn">Novo</span></td><td><button class="btn btn-primary" onclick="showToast('Chamado aceito.', 'success')">Aceitar</button></td></tr>
        <tr><td>Rafael Costa</td><td>Reparo hidraulico</td><td>Batel</td><td><span class="badge badge-success">Aceito</span></td><td><a class="btn btn-secondary" href="agenda.php">Agenda</a></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
