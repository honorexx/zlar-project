<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Cadastro de equipe', 'admin', 'equipe'); ?>
<main class="page-wrap narrow">
  <div class="page-title-block">
    <div class="page-tag">Admin</div>
    <h1 class="page-title">Cadastrar equipe de suporte</h1>
    <p class="page-subtitle">Adicione pessoas que vao atender moradores e prestadores.</p>
  </div>
  <section class="card">
    <div class="form-grid">
      <div class="field col-span-2"><label>Nome completo</label><input type="text" placeholder="Ex.: Ana Paula Ferreira"></div>
      <div class="field"><label>E-mail</label><input type="email" placeholder="ana@zlar.com.br"></div>
      <div class="field"><label>Telefone</label><input type="tel" oninput="maskTel(this)" placeholder="(41) 99999-0000"></div>
      <div class="field"><label>Cargo</label><select><option>Analista</option><option>Suporte</option><option>Supervisor</option><option>Administrador</option></select></div>
      <div class="field"><label>Status</label><select><option>Ativo</option><option>Inativo</option></select></div>
    </div>
    <div class="actions-row actions-row-end"><a class="btn btn-primary" href="equipe-lista.php">Cadastrar</a></div>
  </section>
</main>
<?php zlar_footer(); ?>
