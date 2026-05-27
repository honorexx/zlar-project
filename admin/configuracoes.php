<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Configuracoes administrativas', 'admin', 'configuracoes'); ?>
<main class="page-wrap narrow">
  <div class="page-title-block">
    <div class="page-tag">Admin</div>
    <h1 class="page-title">Configuracoes</h1>
    <p class="page-subtitle">Parametros gerais da plataforma.</p>
  </div>
  <section class="card">
    <div class="form-grid">
      <div class="field"><label>Taxa da plataforma (%)</label><input type="text" value="12"></div>
      <div class="field"><label>Prazo de repasse</label><select><option>7 dias</option><option>15 dias</option><option>30 dias</option></select></div>
      <div class="field col-span-2"><label>E-mail de suporte</label><input type="email" value="suporte@zlar.com.br"></div>
    </div>
    <div class="actions-row actions-row-end"><button class="btn btn-primary" onclick="showToast('Configuracoes salvas.', 'success')">Salvar configuracoes</button></div>
  </section>
</main>
<?php zlar_footer(); ?>
