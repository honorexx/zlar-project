<?php $basePath = '..'; require_once __DIR__ . '/../includes/layout.php'; zlar_header('Painel do morador', 'morador', 'painel'); ?>
<main class="page-wrap">
  <div class="dashboard-head">
    <div><div class="page-tag">Painel do morador</div><h1 class="page-title">Minha area</h1><p class="page-subtitle">Acompanhe pedidos e encontre prestadores.</p></div>
    <a class="btn btn-secondary" href="login.php">Sair</a>
  </div>
  <div class="dashboard-grid">
    <div class="metric-card"><span>Solicitacoes abertas</span><strong>1</strong></div>
    <div class="metric-card"><span>Servicos concluidos</span><strong>3</strong></div>
    <div class="metric-card"><span>Enderecos</span><strong>1</strong></div>
  </div>
  <div class="module-grid">
    <a class="module-card" href="solicitar.php"><strong>Solicitar servico</strong><span>Escolher categoria e descrever necessidade.</span></a>
    <a class="module-card" href="historico.php"><strong>Historico</strong><span>Consultar pedidos feitos na plataforma.</span></a>
    <a class="module-card" href="cadastro.php"><strong>Meus dados</strong><span>Atualizar cadastro e endereco.</span></a>
  </div>
</main>
<?php zlar_footer(); ?>
