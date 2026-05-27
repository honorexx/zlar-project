<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Equipe de suporte', 'admin', 'equipe'); ?>
<main class="page-wrap">
  <div class="dashboard-head">
    <div><div class="page-tag">Admin</div><h1 class="page-title">Equipe de suporte</h1><p class="page-subtitle">Membros internos cadastrados.</p></div>
    <a class="btn btn-primary" href="equipe-cadastro.php">Novo membro</a>
  </div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Nome</th><th>E-mail</th><th>Cargo</th><th>Status</th><th>Acoes</th></tr></thead>
      <tbody>
        <tr><td>Ana Paula</td><td>ana@zlar.com.br</td><td>Suporte</td><td><span class="badge badge-success">Ativo</span></td><td><button class="btn btn-warn">Editar</button></td></tr>
        <tr><td>Joao Lima</td><td>joao@zlar.com.br</td><td>Supervisor</td><td><span class="badge badge-success">Ativo</span></td><td><button class="btn btn-warn">Editar</button></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
