<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Moradores', 'admin', 'moradores'); ?>
<main class="page-wrap">
  <div class="page-title-block"><div class="page-tag">Admin</div><h1 class="page-title">Moradores cadastrados</h1><p class="page-subtitle">Usuarios que contratam servicos.</p></div>
  <section class="card table-card">
    <table>
      <thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Status</th></tr></thead>
      <tbody>
        <tr><td>Mariana Alves</td><td>mariana@email.com</td><td>(41) 97777-0000</td><td><span class="badge badge-success">Ativo</span></td></tr>
        <tr><td>Rafael Costa</td><td>rafael@email.com</td><td>(41) 96666-0000</td><td><span class="badge badge-success">Ativo</span></td></tr>
      </tbody>
    </table>
  </section>
</main>
<?php zlar_footer(); ?>
