<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Prestadores', 'admin', 'prestadores'); ?>
<main class="page-wrap">
  <div class="dashboard-head">
    <div><div class="page-tag">Admin</div><h1 class="page-title">Prestadores cadastrados</h1><p class="page-subtitle">Profissionais disponiveis na plataforma.</p></div>
    <a class="btn btn-primary" href="prestadores-cadastro.php">Novo prestador</a>
  </div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Nome</th><th>Servico</th><th>Telefone</th><th>Status</th><th>Acoes</th></tr></thead>
      <tbody>
        <tr><td>Carlos Souza</td><td>Eletricista</td><td>(41) 99999-0000</td><td><span class="badge badge-success">Aprovado</span></td><td><button class="btn btn-warn">Editar</button></td></tr>
        <tr><td>Bruna Silva</td><td>Limpeza</td><td>(41) 98888-0000</td><td><span class="badge badge-warn">Em analise</span></td><td><button class="btn btn-warn">Editar</button></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
