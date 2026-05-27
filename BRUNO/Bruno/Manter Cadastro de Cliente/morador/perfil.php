<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Perfil do morador', 'morador', 'perfil'); ?>
<main class="page-wrap narrow">
  <div class="page-title-block">
    <div class="page-tag">Morador</div>
    <h1 class="page-title">Meu perfil</h1>
    <p class="page-subtitle">Atualize dados pessoais, contato e endereco principal.</p>
  </div>
  <section class="card">
    <div class="section-label">Dados pessoais</div>
    <div class="form-grid">
      <div class="field col-span-2"><label>Nome completo</label><input type="text" value="Mariana Alves"></div>
      <div class="field"><label>E-mail</label><input type="email" value="mariana@email.com"></div>
      <div class="field"><label>Telefone</label><input type="tel" value="(41) 97777-0000" oninput="maskTel(this)"></div>
      <div class="field col-span-2"><label>Endereco principal</label><input type="text" value="Rua das Flores, 120 - Curitiba"></div>
    </div>
    <div class="actions-row actions-row-end"><button class="btn btn-primary" onclick="showToast('Perfil atualizado.', 'success')">Salvar dados</button></div>
  </section>
</main>
<?php zlar_footer(); ?>
