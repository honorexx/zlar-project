<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Cadastro de prestador', 'admin', 'prestadores'); ?>
<main class="page-wrap narrow">
  <div class="page-title-block"><div class="page-tag">Admin</div><h1 class="page-title">Cadastrar prestador</h1><p class="page-subtitle">Cadastro manual feito pela administracao.</p></div>
  <section class="card">
    <div class="form-grid">
      <div class="field col-span-2"><label>Nome completo</label><input type="text"></div>
      <div class="field"><label>Servico</label><input type="text"></div>
      <div class="field"><label>Telefone</label><input type="tel" oninput="maskTel(this)"></div>
      <div class="field"><label>E-mail</label><input type="email"></div>
      <div class="field"><label>Status</label><select><option>Em analise</option><option>Aprovado</option><option>Bloqueado</option></select></div>
    </div>
    <div class="actions-row actions-row-end"><a class="btn btn-primary" href="prestadores-lista.php">Cadastrar</a></div>
  </section>
</main>
<?php zlar_footer(); ?>
