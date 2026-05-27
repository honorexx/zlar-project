<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Solicitar servico', 'morador', 'solicitar'); ?>
<main class="page-wrap narrow">
  <div class="page-title-block">
    <div class="page-tag">Morador</div>
    <h1 class="page-title">Solicitar servico</h1>
    <p class="page-subtitle">Informe o tipo de atendimento que voce precisa.</p>
  </div>
  <section class="card">
    <div class="form-grid">
      <div class="field"><label>Categoria</label><select><option>Eletricista</option><option>Encanador</option><option>Limpeza</option><option>Pintura</option></select></div>
      <div class="field"><label>Data desejada</label><input type="date"></div>
      <div class="field col-span-2"><label>Descricao</label><textarea placeholder="Descreva o problema ou servico desejado"></textarea></div>
      <div class="field col-span-2"><label>Endereco</label><input type="text" placeholder="Endereco do atendimento"></div>
    </div>
    <div class="actions-row actions-row-end"><a class="btn btn-primary" href="historico.php">Enviar solicitacao</a></div>
  </section>
</main>
<?php zlar_footer(); ?>
