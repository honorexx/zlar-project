<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Perfil do prestador', 'prestador', 'perfil'); ?>
<main class="page-wrap narrow">
  <div class="page-title-block">
    <div class="page-tag">Prestador</div>
    <h1 class="page-title">Meu perfil profissional</h1>
    <p class="page-subtitle">Dados exibidos para moradores e administradores.</p>
  </div>
  <section class="rating-summary">
    <div class="metric-card"><span>Nota media</span><strong id="prestador-nota-media">-</strong></div>
    <div class="metric-card"><span>Avaliacoes recebidas</span><strong id="prestador-total-avaliacoes">0</strong></div>
  </section>
  <section class="card">
    <div class="form-grid">
      <div class="field col-span-2"><label>Nome completo</label><input type="text" value="Carlos Souza"></div>
      <div class="field"><label>Servico principal</label><input type="text" value="Eletricista"></div>
      <div class="field"><label>Telefone</label><input type="tel" value="(41) 99999-0000" oninput="maskTel(this)"></div>
      <div class="field col-span-2"><label>Descricao profissional</label><textarea>Atendimento residencial, manutencao preventiva e instalacoes eletricas.</textarea></div>
    </div>
    <div class="actions-row actions-row-end"><button class="btn btn-primary" onclick="showToast('Perfil profissional atualizado.', 'success')">Salvar perfil</button></div>
  </section>
</main>
<?php zlar_footer(); ?>
