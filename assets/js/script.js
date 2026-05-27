// â”€â”€ DATA STORE â”€â”€
var equipe = [];
var prestadores = [];
var editTarget = null;
var editType = null;
var SERVICOS_ZLAR = ['Limpeza pesada', 'Limpeza diaria', 'Baba', 'Cuidador de idosos'];

function saveLocal(key, data) {
  localStorage.setItem(key, JSON.stringify(data));
}

function getLocal(key) {
  try {
    return JSON.parse(localStorage.getItem(key) || 'null');
  } catch (error) {
    return null;
  }
}

function getList(key) {
  return getLocal(key) || [];
}

function addLocalList(key, item) {
  var list = getList(key);
  list.push(item);
  saveLocal(key, list);
}

function normalizeService(value) {
  return String(value || '')
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function isServicoValido(value) {
  return SERVICOS_ZLAR.map(normalizeService).indexOf(normalizeService(value)) !== -1;
}

function servicoAtualPrestador() {
  var prestador = getLocal('zlar_prestador_atual') || {};
  return prestador.servico || '';
}

function updateSolicitacao(id, changes) {
  var solicitacoes = getList('zlar_solicitacoes');
  solicitacoes = solicitacoes.map(function(s) {
    if (String(s.id) !== String(id)) return s;
    Object.keys(changes).forEach(function(key) { s[key] = changes[key]; });
    return s;
  });
  saveLocal('zlar_solicitacoes', solicitacoes);
}

function statusLabel(status) {
  var labels = {
    aberta: 'Novo',
    aceita: 'Aceito',
    em_andamento: 'Em andamento',
    concluida: 'Concluido',
    pagamento_solicitado: 'Pagamento solicitado',
    pago: 'Pago'
  };
  return labels[status] || status || 'Novo';
}

function apiBase() {
  var path = window.location.pathname;
  if (path.indexOf('/morador/') !== -1 || path.indexOf('/prestador/') !== -1 || path.indexOf('/admin/') !== -1) {
    return '../api/';
  }
  return 'api/';
}

function apiPost(endpoint, data) {
  return fetch(apiBase() + endpoint, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  }).then(function(response) {
    return response.text().then(function(text) {
      var json = null;
      if (text) {
        try {
          json = JSON.parse(text);
        } catch (error) {
          throw new Error('API sem JSON. Verifique se o PHP esta rodando no servidor local.');
        }
      } else {
        throw new Error('API sem resposta. Verifique se o PHP esta rodando no servidor local.');
      }
      if (!response.ok || json.ok === false) {
        throw new Error(json.message || 'Erro na requisicao.');
      }
      return json;
    });
  });
}

function isPhpUnavailable(error) {
  return error instanceof TypeError || /Failed to fetch|NetworkError|Unexpected token|JSON|API sem resposta|API sem JSON|indisponivel/i.test(error.message || '');
}

function todayIso() {
  return new Date().toISOString().slice(0, 10);
}

function markInvalid(id, invalid) {
  var el = document.getElementById(id);
  if (el) el.classList.toggle('field-error', !!invalid);
}

function validEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

function validPhone(value) {
  return /^\(\d{2}\) \d{4,5}-\d{4}$/.test(value);
}

function validCpf(value) {
  return /^\d{3}\.\d{3}\.\d{3}-\d{2}$/.test(value);
}

function validPassword(value) {
  return value.length >= 8 && /[A-Z]/.test(value) && /\d/.test(value);
}

function validateDateNotFuture(id, label) {
  var value = document.getElementById(id).value;
  var invalid = !value || value > todayIso();
  markInvalid(id, invalid);
  if (invalid) showToast((label || 'Data') + ' nao pode ser vazia nem futura.', 'error');
  return !invalid;
}

function validatePasswords(senhaId, confirmarId) {
  var senha = document.getElementById(senhaId).value;
  var confirmar = document.getElementById(confirmarId).value;
  var invalid = !validPassword(senha) || senha !== confirmar;
  markInvalid(senhaId, invalid);
  markInvalid(confirmarId, invalid);
  if (!validPassword(senha)) showToast('A senha deve ter 8 caracteres, uma letra maiuscula e um numero.', 'error');
  else if (senha !== confirmar) showToast('A confirmacao de senha nao confere.', 'error');
  return !invalid;
}

function validateMoradorCadastro() {
  var checks = [
    ['morador-nome', function(v) { return v.trim().length >= 3; }, 'Informe o nome completo.'],
    ['morador-email', validEmail, 'Informe um e-mail valido.'],
    ['morador-telefone', validPhone, 'Informe o telefone no formato (00) 00000-0000.'],
    ['morador-cpf', validCpf, 'Informe o CPF no formato 000.000.000-00.'],
    ['morador-endereco', function(v) { return v.trim().length >= 5; }, 'Informe o endereco completo.']
  ];
  for (var i = 0; i < checks.length; i++) {
    var el = document.getElementById(checks[i][0]);
    var ok = el && checks[i][1](el.value);
    markInvalid(checks[i][0], !ok);
    if (!ok) { showToast(checks[i][2], 'error'); return false; }
  }
  return validateDateNotFuture('morador-nascimento', 'Data de nascimento') &&
    validatePasswords('morador-senha', 'morador-confirmar-senha');
}

function validatePrestadorCadastro() {
  var checks = [
    ['prestador-nome', function(v) { return v.trim().length >= 3; }, 'Informe o nome completo.'],
    ['prestador-email', validEmail, 'Informe um e-mail valido.'],
    ['prestador-telefone', validPhone, 'Informe o telefone no formato (00) 00000-0000.'],
    ['prestador-servico', function(v) { return !!v; }, 'Escolha um servico.']
  ];
  for (var i = 0; i < checks.length; i++) {
    var el = document.getElementById(checks[i][0]);
    var ok = el && checks[i][1](el.value);
    markInvalid(checks[i][0], !ok);
    if (!ok) { showToast(checks[i][2], 'error'); return false; }
  }
  return validateDateNotFuture('prestador-nascimento', 'Data de nascimento') &&
    validatePasswords('prestador-senha', 'prestador-confirmar-senha');
}

function loginHtml(event, tipo) {
  event.preventDefault();
  if (tipo === 'admin') {
    var user = document.getElementById('la-user').value.trim();
    var codigo = document.getElementById('la-codigo').value.trim();
    apiPost('login.php', { tipo: 'admin', email: user, senha: codigo })
      .then(function(data) {
        if (data.user) saveLocal('zlar_admin_atual', data.user);
        window.location.href = 'painel.html';
      })
      .catch(function(error) { showAlert('alertLoginAdmin', error.message, 'error'); });
    return;
  }
  var ids = {
    morador: ['lm-email', 'lm-senha', 'alertLoginMorador', 'painel.html'],
    prestador: ['lp-email', 'lp-senha', 'alertLoginPrestador', 'painel.html']
  };
  var cfg = ids[tipo];
  var email = document.getElementById(cfg[0]).value.trim();
  var senha = document.getElementById(cfg[1]).value.trim();
  apiPost('login.php', { tipo: tipo, email: email, senha: senha })
    .then(function(data) {
      if (data.user) saveLocal('zlar_' + tipo + '_atual', data.user);
      window.location.href = cfg[3];
    })
    .catch(function(error) {
      showAlert(cfg[2], error.message, 'error');
    });
}

function esqueciSenhaHtml(event, tipo) {
  event.preventDefault();
  var email = document.getElementById('rec-email').value.trim();
  apiPost('esqueci_senha.php', { email: email, tipo: tipo })
    .then(function(data) {
      showToast('Codigo gerado: ' + data.codigo, 'success');
    })
    .catch(function(error) {
      showToast(error.message, 'error');
    });
}

function cadastrarMoradorHtml(event) {
  event.preventDefault();
  if (!validateMoradorCadastro()) return;
  var morador = {
    nome: document.getElementById('morador-nome').value.trim(),
    email: document.getElementById('morador-email').value.trim(),
    telefone: document.getElementById('morador-telefone').value.trim(),
    cpf: document.getElementById('morador-cpf').value.trim(),
    senha: document.getElementById('morador-senha').value.trim(),
    confirmar_senha: document.getElementById('morador-confirmar-senha').value.trim(),
    nascimento: document.getElementById('morador-nascimento').value,
    endereco: document.getElementById('morador-endereco').value.trim()
  };
  apiPost('cadastrar_morador.php', morador).then(function(data) {
    if (data.user) saveLocal('zlar_morador_atual', data.user);
    addLocalList('zlar_moradores', data.user || morador);
    showToast('Morador cadastrado com sucesso.', 'success');
    setTimeout(function() { window.location.href = 'cadastro-sucesso.html'; }, 800);
  }).catch(function(error) {
    showToast(error.message, 'error');
  });
}

function cadastrarPrestadorHtml(event) {
  event.preventDefault();
  if (!validatePrestadorCadastro()) return;
  var prestador = {
    nome: document.getElementById('prestador-nome').value.trim(),
    email: document.getElementById('prestador-email').value.trim(),
    telefone: document.getElementById('prestador-telefone').value.trim(),
    senha: document.getElementById('prestador-senha').value.trim(),
    confirmar_senha: document.getElementById('prestador-confirmar-senha').value.trim(),
    nascimento: document.getElementById('prestador-nascimento').value,
    servico: document.getElementById('prestador-servico').value.trim(),
    descricao: document.getElementById('prestador-descricao').value.trim()
  };
  apiPost('cadastrar_prestador.php', prestador).then(function(data) {
    if (data.user) saveLocal('zlar_prestador_atual', data.user);
    addLocalList('zlar_prestadores', data.user || prestador);
    showToast('Prestador cadastrado com sucesso.', 'success');
    setTimeout(function() { window.location.href = 'cadastro-sucesso.html'; }, 800);
  }).catch(function(error) {
    showToast(error.message, 'error');
  });
}

function criarSolicitacaoHtml(event) {
  event.preventDefault();
  var morador = getLocal('zlar_morador_atual') || {};
  var dataDesejada = document.getElementById('sol-data').value;
  if (dataDesejada && dataDesejada < todayIso()) {
    showToast('A data desejada nao pode ser anterior a hoje.', 'error');
    return;
  }
  var solicitacao = {
    id: Date.now(),
    morador: morador.nome || 'Morador',
    categoria: document.getElementById('sol-categoria').value,
    data: dataDesejada,
    endereco: document.getElementById('sol-endereco').value.trim(),
    descricao: document.getElementById('sol-descricao').value.trim(),
    status: 'aberta',
    prestador: '',
    pagamento: 'pendente'
  };
  apiPost('criar_solicitacao.php', solicitacao).then(function() {
    addLocalList('zlar_solicitacoes', solicitacao);
    showToast('Solicitacao enviada.', 'success');
    setTimeout(function() { window.location.href = 'historico.html'; }, 800);
  }).catch(function(error) {
    showToast(error.message, 'error');
  });
}

function cadastrarEquipeHtml(event) {
  event.preventDefault();
  apiPost('cadastrar_equipe.php', {
    nome: document.getElementById('eq-html-nome').value.trim(),
    email: document.getElementById('eq-html-email').value.trim(),
    cargo: document.getElementById('eq-html-cargo').value
  }).then(function() {
    showToast('Membro cadastrado.', 'success');
    event.target.reset();
  }).catch(function(error) { showToast(error.message, 'error'); });
}

function fillValue(id, value) {
  var el = document.getElementById(id);
  if (el) el.value = value || '';
}

function populateMoradorProfile() {
  var morador = getLocal('zlar_morador_atual');
  if (!morador) return;
  fillValue('perfil-morador-nome', morador.nome);
  fillValue('perfil-morador-email', morador.email);
  fillValue('perfil-morador-telefone', morador.telefone);
  fillValue('perfil-morador-endereco', morador.endereco);
}

function salvarPerfilMorador() {
  var morador = {
    nome: document.getElementById('perfil-morador-nome').value.trim(),
    email: document.getElementById('perfil-morador-email').value.trim(),
    telefone: document.getElementById('perfil-morador-telefone').value.trim(),
    endereco: document.getElementById('perfil-morador-endereco').value.trim()
  };
  saveLocal('zlar_morador_atual', morador);
  showToast('Perfil atualizado.', 'success');
}

function populatePrestadorProfile() {
  var prestador = getLocal('zlar_prestador_atual');
  if (!prestador) return;
  fillValue('perfil-prestador-nome', prestador.nome);
  fillValue('perfil-prestador-servico', prestador.servico);
  fillValue('perfil-prestador-telefone', prestador.telefone);
  fillValue('perfil-prestador-descricao', prestador.descricao);
}

function salvarPerfilPrestador() {
  var prestador = {
    nome: document.getElementById('perfil-prestador-nome').value.trim(),
    servico: document.getElementById('perfil-prestador-servico').value.trim(),
    telefone: document.getElementById('perfil-prestador-telefone').value.trim(),
    descricao: document.getElementById('perfil-prestador-descricao').value.trim()
  };
  var antigo = getLocal('zlar_prestador_atual') || {};
  prestador.email = antigo.email || '';
  saveLocal('zlar_prestador_atual', prestador);
  showToast('Perfil profissional atualizado.', 'success');
}

function renderAdminMoradores() {
  var tb = document.getElementById('tbMoradores');
  if (!tb) return;
  var moradores = getList('zlar_moradores');
  if (!moradores.length) return;
  tb.innerHTML = moradores.map(function(m) {
    return '<tr><td>' + esc(m.nome) + '</td><td>' + esc(m.email) + '</td><td>' + esc(m.telefone) + '</td><td><span class="badge badge-success">Ativo</span></td></tr>';
  }).join('');
}

function renderAdminPrestadores() {
  var tb = document.getElementById('tbPrestadoresAdmin');
  if (!tb) return;
  var prestadoresLocal = getList('zlar_prestadores');
  if (!prestadoresLocal.length) return;
  tb.innerHTML = prestadoresLocal.map(function(p) {
    return '<tr><td>' + esc(p.nome) + '</td><td>' + esc(p.servico) + '</td><td>' + esc(p.telefone) + '</td><td><span class="badge badge-warn">Em analise</span></td></tr>';
  }).join('');
}

function renderChamadosPrestador() {
  var tb = document.getElementById('tbChamados');
  if (!tb) return;
  var prestador = getLocal('zlar_prestador_atual') || {};
  var servicoPrestador = normalizeService(prestador.servico);
  var labelServico = document.getElementById('prestador-servico-atual');
  if (labelServico) labelServico.textContent = prestador.servico || 'Nenhum servico selecionado no perfil';
  if (!servicoPrestador) {
    tb.innerHTML = '<tr><td colspan="5"><div class="empty-state"><h3>Complete seu perfil profissional</h3><p>Escolha se voce atende limpeza pesada, limpeza diaria, baba ou cuidador de idosos.</p><a class="btn btn-primary" href="perfil.html">Atualizar perfil</a></div></td></tr>';
    return;
  }
  var solicitacoes = getList('zlar_solicitacoes');
  var compativeis = solicitacoes.filter(function(s) {
    return isServicoValido(s.categoria) && normalizeService(s.categoria) === servicoPrestador;
  });
  if (!compativeis.length) return;
  tb.innerHTML = compativeis.map(function(s) {
    return '<tr><td>' + esc(s.morador) + '</td><td>' + esc(s.categoria) + '</td><td>' + esc(s.data || '-') + '</td><td>' + esc(s.endereco) + '</td><td><span class="badge badge-warn">' + esc(statusLabel(s.status)) + '</span></td><td>' + actionPrestador(s) + '</td></tr>';
  }).join('');
}

function renderHistoricoMorador() {
  var tb = document.getElementById('historicoMorador');
  if (!tb) return;
  var solicitacoes = getList('zlar_solicitacoes').filter(function(s) { return isServicoValido(s.categoria); });
  if (!solicitacoes.length) return;
  tb.innerHTML = solicitacoes.map(function(s) {
    return '<tr><td>' + esc(s.categoria) + '</td><td>' + esc(s.data || '-') + '</td><td><span class="badge badge-warn">' + esc(statusLabel(s.status)) + '</span></td><td>' + esc(s.prestador || 'A definir') + '</td><td>' + prestadorInfoHtml(s) + '</td><td>' + actionMorador(s) + '</td></tr>';
  }).join('');
}

function prestadorInfoHtml(s) {
  if (!s.prestador) return '<span class="td-muted">Aguardando aceite</span>';
  var linhas = [
    '<strong>' + esc(s.prestador) + '</strong>',
    s.prestadorServico ? 'Servico: ' + esc(s.prestadorServico) : '',
    s.prestadorTelefone ? 'Telefone: ' + esc(s.prestadorTelefone) : '',
    s.prestadorDescricao ? 'Descricao: ' + esc(s.prestadorDescricao) : ''
  ].filter(Boolean);
  return '<div class="provider-info">' + linhas.join('<br>') + '</div>';
}

function pagamentoInfoHtml(s) {
  if (s.status !== 'pagamento_solicitado' && s.status !== 'pago') return '';
  var linhas = [
    '<strong>Pagamento</strong>',
    s.valor ? 'Valor: R$ ' + esc(s.valor) : '',
    s.pix ? 'Pix: ' + esc(s.pix) : '',
    s.observacaoPagamento ? 'Obs.: ' + esc(s.observacaoPagamento) : ''
  ].filter(Boolean);
  return '<div class="payment-info">' + linhas.join('<br>') + '</div>';
}

function getAvaliacoes() {
  return getList('zlar_avaliacoes');
}

function prestadorKeyFrom(data) {
  return normalizeService((data && data.prestadorEmail) || (data && data.email) || (data && data.prestador) || (data && data.nome) || '');
}

function avaliacoesDoPrestador(prestador) {
  var key = prestadorKeyFrom(prestador || getLocal('zlar_prestador_atual') || {});
  if (!key) return [];
  return getAvaliacoes().filter(function(a) { return prestadorKeyFrom(a) === key; });
}

function resumoAvaliacoes(prestador) {
  var avaliacoes = avaliacoesDoPrestador(prestador);
  var total = avaliacoes.length;
  var soma = avaliacoes.reduce(function(acc, a) { return acc + Number(a.nota || 0); }, 0);
  return {
    total: total,
    media: total ? soma / total : 0,
    estrelas: [5, 4, 3, 2, 1].map(function(nota) {
      return {
        nota: nota,
        total: avaliacoes.filter(function(a) { return Number(a.nota) === nota; }).length
      };
    })
  };
}

function formatNota(value) {
  return Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
}

function starsHtml(nota) {
  var n = Number(nota || 0);
  var html = '';
  for (var i = 1; i <= 5; i++) html += '<span class="' + (i <= n ? 'filled' : '') + '">★</span>';
  return '<span class="stars-readonly" aria-label="' + n + ' de 5 estrelas">' + html + '</span>';
}

function atualizarResumoPrestador() {
  var prestador = getLocal('zlar_prestador_atual') || {};
  var resumo = resumoAvaliacoes(prestador);
  var mediaEl = document.getElementById('prestador-nota-media');
  var totalEl = document.getElementById('prestador-total-avaliacoes');
  var distEl = document.getElementById('prestador-distribuicao-avaliacoes');
  if (mediaEl) mediaEl.textContent = resumo.total ? formatNota(resumo.media) : '-';
  if (totalEl) totalEl.textContent = resumo.total;
  if (distEl) {
    distEl.innerHTML = resumo.estrelas.map(function(item) {
      return '<span>' + item.nota + ' estrelas: <strong>' + item.total + '</strong></span>';
    }).join('');
  }
  if (prestador.nome || prestador.email) {
    prestador.notaMedia = resumo.media;
    prestador.totalAvaliacoes = resumo.total;
    saveLocal('zlar_prestador_atual', prestador);
  }
}

function actionPrestador(s) {
  if (s.status === 'aberta') {
    return '<button class="btn btn-primary" onclick="aceitarChamado(' + s.id + ')">Aceitar</button>';
  }
  if (s.status === 'aceita') {
    return '<button class="btn btn-primary" onclick="iniciarServico(' + s.id + ')">Iniciar servico</button>';
  }
  if (s.status === 'em_andamento') {
    return '<span class="td-muted">Aguardando finalizacao do morador</span>';
  }
  if (s.status === 'concluida') {
    return '<button class="btn btn-secondary" onclick="abrirPainelPagamento(' + s.id + ')">Informar valor e Pix</button>';
  }
  if (s.status === 'pagamento_solicitado') {
    return '<span class="badge badge-warn">Aguardando pagamento</span>';
  }
  if (s.status === 'pago') {
    return '<span class="badge badge-success">Recebido</span>';
  }
  return '';
}

function actionMorador(s) {
  if (s.status === 'em_andamento') {
    return '<button class="btn btn-primary" onclick="finalizarServicoMorador(' + s.id + ')">Finalizar servico</button>';
  }
  if (s.status === 'pagamento_solicitado') {
    return pagamentoInfoHtml(s) + '<button class="btn btn-primary" onclick="pagarSolicitacao(' + s.id + ')">Pagar</button>';
  }
  if (s.status === 'pago') {
    if (s.avaliacaoStatus === 'enviada') {
      return pagamentoInfoHtml(s) + '<span class="badge badge-success">Pago e avaliado</span>';
    }
    if (s.avaliacaoStatus === 'dispensada') {
      return pagamentoInfoHtml(s) + '<span class="badge badge-success">Pago</span>';
    }
    return pagamentoInfoHtml(s) + '<div class="td-actions"><span class="badge badge-success">Pago</span><button class="btn btn-secondary" onclick="abrirAvaliacaoPagamento(' + s.id + ')">Avaliar</button></div>';
  }
  return '<span class="td-muted">Sem acao</span>';
}

function aceitarChamado(id) {
  var prestador = getLocal('zlar_prestador_atual') || {};
  updateSolicitacao(id, {
    status: 'aceita',
    prestador: prestador.nome || 'Prestador',
    prestadorServico: prestador.servico || '',
    prestadorTelefone: prestador.telefone || '',
    prestadorEmail: prestador.email || '',
    prestadorDescricao: prestador.descricao || ''
  });
  renderChamadosPrestador();
  showToast('Chamado aceito.', 'success');
}

function iniciarServico(id) {
  updateSolicitacao(id, { status: 'em_andamento' });
  renderChamadosPrestador();
  showToast('Servico iniciado.', 'success');
}

function finalizarServicoMorador(id) {
  updateSolicitacao(id, { status: 'concluida' });
  renderHistoricoMorador();
  showToast('Servico finalizado. Aguarde o prestador solicitar pagamento.', 'success');
}

function abrirPainelPagamento(id) {
  var panel = document.getElementById('paymentPanel');
  if (!panel) return;
  document.getElementById('pagamento-solicitacao-id').value = id;
  document.getElementById('pagamento-valor').value = '';
  document.getElementById('pagamento-pix').value = '';
  document.getElementById('pagamento-observacao').value = '';
  panel.classList.add('open');
  panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function fecharPainelPagamento() {
  var panel = document.getElementById('paymentPanel');
  if (panel) panel.classList.remove('open');
}

function enviarSolicitacaoPagamento() {
  var id = document.getElementById('pagamento-solicitacao-id').value;
  var valor = document.getElementById('pagamento-valor').value.trim();
  var pix = document.getElementById('pagamento-pix').value.trim();
  var observacao = document.getElementById('pagamento-observacao').value.trim();
  if (!valor || !pix) {
    showToast('Informe o valor e a chave Pix.', 'error');
    return;
  }
  updateSolicitacao(id, {
    status: 'pagamento_solicitado',
    pagamento: 'solicitado',
    valor: valor,
    pix: pix,
    observacaoPagamento: observacao
  });
  fecharPainelPagamento();
  renderChamadosPrestador();
  showToast('Pagamento enviado para o morador.', 'success');
}

function pagarSolicitacao(id) {
  updateSolicitacao(id, { status: 'pago', pagamento: 'pago' });
  renderHistoricoMorador();
  showToast('Pagamento registrado.', 'success');
  abrirAvaliacaoPagamento(id);
}

function encontrarSolicitacao(id) {
  return getList('zlar_solicitacoes').find(function(s) { return String(s.id) === String(id); });
}

function abrirAvaliacaoPagamento(id) {
  var solicitacao = encontrarSolicitacao(id);
  if (!solicitacao || !solicitacao.prestador) return;
  var antigo = document.getElementById('avaliacaoOverlay');
  if (antigo) antigo.remove();
  var overlay = document.createElement('div');
  overlay.className = 'modal-overlay open';
  overlay.id = 'avaliacaoOverlay';
  overlay.innerHTML =
    '<div class="modal rating-modal">' +
      '<div class="modal-header"><div><div class="section-label">Avaliacao do cliente</div><h2 class="modal-title">Como foi o atendimento?</h2></div><button class="modal-close" onclick="fecharAvaliacaoModal()">×</button></div>' +
      '<p class="page-subtitle">Sua avaliacao fica salva no perfil de ' + esc(solicitacao.prestador) + ' e ajuda outros moradores.</p>' +
      '<div id="avaliacaoEscolha" class="rating-choice actions-row"><button class="btn btn-primary" onclick="mostrarFormularioAvaliacao(' + id + ')">Sim, avaliar</button><button class="btn btn-secondary" onclick="dispensarAvaliacao(' + id + ')">Nao avaliar</button></div>' +
      '<form id="avaliacaoForm" class="rating-form" onsubmit="salvarAvaliacaoSolicitacao(event, ' + id + ')">' +
        '<input type="hidden" id="avaliacaoNota" value="5">' +
        '<div class="field"><label>Nota</label><div class="star-input" id="starInput">' + [1,2,3,4,5].map(function(n) { return '<button type="button" class="active" onclick="selecionarNotaAvaliacao(' + n + ')">★</button>'; }).join('') + '</div></div>' +
        '<div class="field"><label>Descricao opcional</label><textarea id="avaliacaoComentario" placeholder="Conte como foi o atendimento"></textarea></div>' +
        '<div class="actions-row actions-row-end"><button type="button" class="btn btn-ghost" onclick="fecharAvaliacaoModal()">Cancelar</button><button class="btn btn-primary" type="submit">Enviar avaliacao</button></div>' +
      '</form>' +
    '</div>';
  document.body.appendChild(overlay);
}

function fecharAvaliacaoModal() {
  var overlay = document.getElementById('avaliacaoOverlay');
  if (overlay) overlay.remove();
}

function mostrarFormularioAvaliacao() {
  var escolha = document.getElementById('avaliacaoEscolha');
  var form = document.getElementById('avaliacaoForm');
  if (escolha) escolha.style.display = 'none';
  if (form) form.classList.add('open');
  selecionarNotaAvaliacao(5);
}

function selecionarNotaAvaliacao(nota) {
  var input = document.getElementById('avaliacaoNota');
  if (input) input.value = nota;
  var botoes = document.querySelectorAll('#starInput button');
  botoes.forEach(function(btn, index) {
    btn.classList.toggle('active', index < nota);
  });
}

function dispensarAvaliacao(id) {
  updateSolicitacao(id, { avaliacaoStatus: 'dispensada' });
  fecharAvaliacaoModal();
  renderHistoricoMorador();
  renderMoradorAvaliacoes();
  showToast('Tudo certo. A avaliacao ficou como opcional.', 'success');
}

function salvarAvaliacaoSolicitacao(event, id) {
  event.preventDefault();
  var solicitacao = encontrarSolicitacao(id);
  if (!solicitacao || !solicitacao.prestador) return;
  var nota = Number(document.getElementById('avaliacaoNota').value || 0);
  if (nota < 1 || nota > 5) {
    showToast('Escolha uma nota de 1 a 5 estrelas.', 'error');
    return;
  }
  var comentario = document.getElementById('avaliacaoComentario').value.trim();
  var morador = getLocal('zlar_morador_atual') || {};
  var avaliacoes = getAvaliacoes().filter(function(a) { return String(a.solicitacaoId) !== String(id); });
  var avaliacao = {
    id: Date.now(),
    solicitacaoId: solicitacao.id,
    morador: morador.nome || solicitacao.morador || 'Morador',
    prestador: solicitacao.prestador,
    prestadorEmail: solicitacao.prestadorEmail || '',
    prestadorServico: solicitacao.prestadorServico || solicitacao.categoria || '',
    servico: solicitacao.categoria || '',
    nota: nota,
    comentario: comentario,
    criadoEm: new Date().toLocaleString('pt-BR')
  };
  avaliacoes.push(avaliacao);
  saveLocal('zlar_avaliacoes', avaliacoes);
  updateSolicitacao(id, { avaliacaoStatus: 'enviada', avaliacaoId: avaliacao.id });
  fecharAvaliacaoModal();
  renderHistoricoMorador();
  renderMoradorAvaliacoes();
  renderPrestadorAvaliacoes();
  atualizarResumoPrestador();
  showToast('Avaliacao enviada. Obrigado pelo feedback.', 'success');
}

function renderMoradorAvaliacoes() {
  var tb = document.getElementById('tbAvaliacoesMorador');
  if (!tb) return;
  var avaliacoes = getAvaliacoes();
  if (!avaliacoes.length) return;
  tb.innerHTML = avaliacoes.map(function(a) {
    return '<tr><td>' + esc(a.servico || '-') + '</td><td>' + esc(a.prestador || '-') + '</td><td>' + starsHtml(a.nota) + ' ' + formatNota(a.nota) + '</td><td>' + (a.comentario ? esc(a.comentario) : '<span class="td-muted">Sem descricao</span>') + '</td><td><span class="badge badge-success">Enviada</span></td></tr>';
  }).join('');
}

function renderPrestadorAvaliacoes() {
  var tb = document.getElementById('tbAvaliacoesPrestador');
  if (!tb) return;
  var avaliacoes = avaliacoesDoPrestador();
  if (!avaliacoes.length) return;
  tb.innerHTML = avaliacoes.map(function(a) {
    return '<tr><td>' + esc(a.morador || '-') + '</td><td>' + esc(a.servico || a.prestadorServico || '-') + '</td><td>' + starsHtml(a.nota) + ' ' + formatNota(a.nota) + '</td><td>' + (a.comentario ? esc(a.comentario) : '<span class="td-muted">Sem descricao</span>') + '</td><td>' + esc(a.criadoEm || '-') + '</td></tr>';
  }).join('');
}

function selecionarProblema(texto) {
  fillValue('suporte-tipo', texto);
}

function abrirChamadoSuporte(event, perfil) {
  event.preventDefault();
  var usuario = perfil === 'prestador'
    ? (getLocal('zlar_prestador_atual') || {})
    : (getLocal('zlar_morador_atual') || {});
  var chamado = {
    id: Date.now(),
    perfil: perfil,
    usuario: usuario.nome || (perfil === 'prestador' ? 'Prestador' : 'Morador'),
    email: usuario.email || '',
    tipo: document.getElementById('suporte-tipo').value.trim(),
    urgencia: document.getElementById('suporte-urgencia').value,
    descricao: document.getElementById('suporte-descricao').value.trim(),
    status: 'aberto',
    resposta: '',
    criadoEm: new Date().toLocaleString('pt-BR')
  };
  apiPost('chamados_suporte.php', Object.assign({ action: 'criar' }, chamado))
    .then(function() {
      addLocalList('zlar_suporte', chamado);
      event.target.reset();
      renderMeusSuportes(perfil);
      showToast('Chamado de suporte aberto.', 'success');
    })
    .catch(function(error) { showToast(error.message, 'error'); });
}

function suporteStatusLabel(status) {
  var labels = { aberto: 'Aberto', em_atendimento: 'Em atendimento', resolvido: 'Resolvido', cancelado: 'Cancelado' };
  return labels[status] || status;
}

function renderMeusSuportes(perfil) {
  var tb = document.getElementById('tbMeusSuportes');
  if (!tb) return;
  var chamados = getList('zlar_suporte').filter(function(c) { return c.perfil === perfil; });
  if (!chamados.length) return;
  tb.innerHTML = chamados.map(function(c) {
    return '<tr><td>' + esc(c.tipo) + '<br><span class="td-muted">' + esc(c.descricao) + '</span></td><td>' + esc(c.urgencia) + '</td><td><span class="badge badge-warn">' + esc(suporteStatusLabel(c.status)) + '</span></td><td>' + (c.resposta ? esc(c.resposta) : '<span class="td-muted">Aguardando suporte</span>') + '</td></tr>';
  }).join('');
}

function renderAdminSuporte() {
  var tb = document.getElementById('tbAdminSuporte');
  if (!tb) return;
  var chamados = getList('zlar_suporte');
  if (!chamados.length) return;
  tb.innerHTML = chamados.map(function(c) {
    return '<tr><td>#' + c.id + '</td><td>' + esc(c.perfil) + '</td><td><strong>' + esc(c.usuario) + '</strong><br><span class="td-muted">' + esc(c.email) + '</span></td><td>' + esc(c.tipo) + '<br><span class="td-muted">' + esc(c.descricao) + '</span></td><td>' + esc(c.urgencia) + '</td><td><span class="badge badge-warn">' + esc(suporteStatusLabel(c.status)) + '</span></td><td><div class="td-actions support-actions"><button class="btn btn-warn" onclick="editarStatusSuporte(' + c.id + ')">Editar</button><button class="btn btn-secondary" onclick="responderSuporte(' + c.id + ')">Responder</button><button class="btn btn-primary" onclick="resolverSuporte(' + c.id + ')">Resolver</button><button class="btn btn-danger" onclick="excluirSuporte(' + c.id + ')">Excluir</button></div></td></tr>';
  }).join('');
}

function updateSuporte(id, changes) {
  var chamados = getList('zlar_suporte').map(function(c) {
    if (String(c.id) !== String(id)) return c;
    Object.keys(changes).forEach(function(key) { c[key] = changes[key]; });
    return c;
  });
  saveLocal('zlar_suporte', chamados);
}

function editarStatusSuporte(id) {
  var status = prompt('Novo status: aberto, em_atendimento, resolvido ou cancelado');
  if (!status) return;
  updateSuporte(id, { status: status });
  renderAdminSuporte();
}

function responderSuporte(id) {
  var resposta = prompt('Digite a resposta para o usuario');
  if (!resposta) return;
  updateSuporte(id, { resposta: resposta, status: 'em_atendimento' });
  renderAdminSuporte();
}

function resolverSuporte(id) {
  updateSuporte(id, { status: 'resolvido' });
  renderAdminSuporte();
  showToast('Chamado marcado como resolvido.', 'success');
}

function excluirSuporte(id) {
  if (!confirm('Excluir este chamado de suporte?')) return;
  var chamados = getList('zlar_suporte').filter(function(c) { return String(c.id) !== String(id); });
  saveLocal('zlar_suporte', chamados);
  renderAdminSuporte();
  showToast('Chamado excluido.', 'error');
}

function renderAdminDashboard() {
  var totalMoradores = document.getElementById('admin-total-moradores');
  var totalPrestadores = document.getElementById('admin-total-prestadores');
  var totalEquipe = document.getElementById('admin-total-equipe');
  if (totalMoradores) totalMoradores.textContent = getList('zlar_moradores').length;
  if (totalPrestadores) totalPrestadores.textContent = getList('zlar_prestadores').length;
  if (totalEquipe) totalEquipe.textContent = getList('zlar_equipe').length;
  var totalSuporte = document.getElementById('admin-total-suporte');
  if (totalSuporte) totalSuporte.textContent = getList('zlar_suporte').length;
}

function renderMoradorDashboard() {
  var abertas = document.getElementById('morador-total-abertas');
  var concluidos = document.getElementById('morador-total-concluidos');
  var pagamentos = document.getElementById('morador-total-pagamentos');
  if (!abertas && !concluidos && !pagamentos) return;
  var solicitacoes = getList('zlar_solicitacoes');
  if (abertas) abertas.textContent = solicitacoes.filter(function(s) {
    return ['aberta', 'aceita', 'em_andamento'].indexOf(s.status) !== -1;
  }).length;
  if (concluidos) concluidos.textContent = solicitacoes.filter(function(s) {
    return ['concluida', 'pagamento_solicitado', 'pago'].indexOf(s.status) !== -1;
  }).length;
  if (pagamentos) pagamentos.textContent = solicitacoes.filter(function(s) {
    return s.status === 'pagamento_solicitado';
  }).length;
}

function renderPrestadorDashboard() {
  var novos = document.getElementById('prestador-total-novos');
  var andamento = document.getElementById('prestador-total-andamento');
  var pagamentos = document.getElementById('prestador-total-pagamentos');
  if (!novos && !andamento && !pagamentos) return;
  var prestador = getLocal('zlar_prestador_atual') || {};
  var servicoPrestador = normalizeService(prestador.servico);
  var compativeis = getList('zlar_solicitacoes').filter(function(s) {
    return isServicoValido(s.categoria) && normalizeService(s.categoria) === servicoPrestador;
  });
  if (novos) novos.textContent = compativeis.filter(function(s) { return s.status === 'aberta'; }).length;
  if (andamento) andamento.textContent = compativeis.filter(function(s) {
    return ['aceita', 'em_andamento'].indexOf(s.status) !== -1;
  }).length;
  if (pagamentos) pagamentos.textContent = compativeis.filter(function(s) {
    return ['concluida', 'pagamento_solicitado', 'pago'].indexOf(s.status) !== -1;
  }).length;
}

function areaFromPath() {
  var path = window.location.pathname.toLowerCase();
  if (path.indexOf('/morador/') !== -1) return 'morador';
  if (path.indexOf('/prestador/') !== -1) return 'prestador';
  if (path.indexOf('/admin/') !== -1) return 'admin';
  return '';
}

function pageNameFromPath() {
  var file = window.location.pathname.split('/').pop() || 'index.html';
  return file.replace(/\.(html|php)$/i, '');
}

function isPublicPage(page) {
  return ['login', 'cadastro', 'cadastro-sucesso', 'esqueci-senha', 'index'].indexOf(page) !== -1;
}

function currentAreaUser(area) {
  return getLocal('zlar_' + area + '_atual');
}

function loginUrl(area) {
  return area ? '../' + area + '/login.html' : 'morador/login.html';
}

function protectHtmlPage() {
  var area = areaFromPath();
  var page = pageNameFromPath();
  if (!area || isPublicPage(page)) return;
  apiPost('session.php', { tipo: area })
    .then(function(data) {
      if (data.user) saveLocal('zlar_' + area + '_atual', data.user);
      renderLoggedIdentity();
    })
    .catch(function() {
      localStorage.removeItem('zlar_' + area + '_atual');
      window.location.href = 'login.html';
    });
}

function renderLoggedIdentity() {
  var area = areaFromPath();
  if (!area || isPublicPage(pageNameFromPath())) return;
  var user = currentAreaUser(area);
  var header = document.querySelector('.zlar-header');
  if (!user || !header || header.querySelector('.user-chip')) return;
  var chip = document.createElement('span');
  chip.className = 'user-chip';
  chip.textContent = (user.nome || user.email || 'Usuario') + ' - ' + area.charAt(0).toUpperCase() + area.slice(1);
  header.appendChild(chip);
}

function wireLogoutLinks() {
  Array.prototype.forEach.call(document.querySelectorAll('a'), function(link) {
    if (link.textContent.trim().toLowerCase() !== 'sair') return;
    link.addEventListener('click', function(event) {
      event.preventDefault();
      var area = areaFromPath();
      localStorage.removeItem('zlar_' + area + '_atual');
      apiPost('logout.php', {}).finally(function() {
        window.location.href = 'login.html';
      });
    });
  });
}

function configureDateLimits() {
  ['morador-nascimento', 'prestador-nascimento'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.max = todayIso();
  });
  var solData = document.getElementById('sol-data');
  if (solData) solData.min = todayIso();
}

document.addEventListener('DOMContentLoaded', function() {
  protectHtmlPage();
  configureDateLimits();
  renderLoggedIdentity();
  wireLogoutLinks();
  populateMoradorProfile();
  populatePrestadorProfile();
  renderAdminMoradores();
  renderAdminPrestadores();
  renderChamadosPrestador();
  renderHistoricoMorador();
  renderMoradorAvaliacoes();
  renderPrestadorAvaliacoes();
  renderAdminDashboard();
  renderMoradorDashboard();
  renderPrestadorDashboard();
  atualizarResumoPrestador();
  renderMeusSuportes('morador');
  renderMeusSuportes('prestador');
  renderAdminSuporte();
});

// â”€â”€ NAVIGATION â”€â”€
function goTo(screen) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.header-nav a').forEach(a => a.classList.remove('active'));
  var target = document.getElementById('screen-' + screen);
  if (!target) return;
  target.classList.add('active');
  var navMap = {
    'home':'nav-home',
    'portal-morador':'nav-morador',
    'login-morador':'nav-morador',
    'dashboard-morador':'nav-morador',
    'cad-morador':'nav-morador',
    'portal-prestador':'nav-prestador',
    'login-prestador':'nav-prestador',
    'dashboard-prestador':'nav-prestador',
    'cad-prestador':'nav-prestador',
    'lista-prestador':'nav-admin',
    'login-admin':'nav-admin',
    'dashboard-admin':'nav-admin',
    'cad-equipe':'nav-admin',
    'lista-equipe':'nav-admin'
  };
  if (navMap[screen]) document.getElementById(navMap[screen]).classList.add('active');
  window.scrollTo(0, 0);

  if (screen === 'lista-equipe')    renderEquipe();
  if (screen === 'lista-prestador') renderPrestadores();
  if (screen === 'dashboard-admin' || screen === 'dashboard-morador') updateDashboardMetrics();
}

function updateDashboardMetrics() {
  var metricEquipe = document.getElementById('metricEquipe');
  var metricPrestadores = document.getElementById('metricPrestadores');
  var metricPrestMorador = document.getElementById('metricPrestMorador');
  if (metricEquipe) metricEquipe.textContent = equipe.length;
  if (metricPrestadores) metricPrestadores.textContent = prestadores.length;
  if (metricPrestMorador) metricPrestMorador.textContent = prestadores.length;
}

function loginPerfil(tipo) {
  var map = {
    morador: { email: 'lm-email', senha: 'lm-senha', alert: 'alertLoginMorador', next: 'dashboard-morador', msg: 'Bem-vindo a area do morador.' },
    prestador: { email: 'lp-email', senha: 'lp-senha', alert: 'alertLoginPrestador', next: 'dashboard-prestador', msg: 'Bem-vindo a area do prestador.' },
    admin: { email: 'la-email', senha: 'la-senha', alert: 'alertLoginAdmin', next: 'dashboard-admin', msg: 'Acesso administrativo liberado.' }
  };
  var cfg = map[tipo];
  var email = document.getElementById(cfg.email).value.trim();
  var senha = document.getElementById(cfg.senha).value.trim();
  if (!email || !senha) {
    showAlert(cfg.alert, 'Preencha e-mail e senha para continuar.', 'error');
    return;
  }
  showToast(cfg.msg, 'success');
  goTo(cfg.next);
}

function entrarPagina(tipo) {
  var map = {
    morador: { email: 'lm-email', senha: 'lm-senha', alert: 'alertLoginMorador', url: 'painel.php' },
    prestador: { email: 'lp-email', senha: 'lp-senha', alert: 'alertLoginPrestador', url: 'painel.php' },
    admin: { email: 'la-email', senha: 'la-senha', alert: 'alertLoginAdmin', url: 'painel.php' }
  };
  var cfg = map[tipo];
  if (!cfg) return;
  var email = document.getElementById(cfg.email).value.trim();
  var senha = document.getElementById(cfg.senha).value.trim();
  if (!email || !senha) {
    showAlert(cfg.alert, 'Preencha e-mail e senha para continuar.', 'error');
    return;
  }
  window.location.href = cfg.url;
}

// â”€â”€ MASKS â”€â”€
function maskTel(el) {
  var v = el.value.replace(/\D/g,'').slice(0,11);
  if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
  else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
  el.value = v;
}
function maskCPF(el) {
  var v = el.value.replace(/\D/g,'').slice(0,11);
  v = v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');
  el.value = v;
}

// â”€â”€ PASSWORD â”€â”€
function checkPw(el) {
  var v = el.value, s = 0;
  if (v.length >= 8) s++;
  if (/[A-Z]/.test(v)) s++;
  if (/[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  ['p1','p2','p3','p4'].forEach(function(id){ document.getElementById(id).className='pw-bar'; });
  var lbl = document.getElementById('pwLabel');
  if (s <= 2) { for(var i=0;i<s;i++) document.getElementById('p'+(i+1)).classList.add('weak'); lbl.textContent='Senha fraca'; lbl.style.color='#D85A30'; }
  else if (s === 3) { for(var i=0;i<3;i++) document.getElementById('p'+(i+1)).classList.add('medium'); lbl.textContent='Senha mÃ©dia'; lbl.style.color='#EF9F27'; }
  else { ['p1','p2','p3','p4'].forEach(function(id){ document.getElementById(id).classList.add('strong'); }); lbl.textContent='Senha forte âœ“'; lbl.style.color='var(--teal)'; }
}

// â”€â”€ MORADOR STEPS â”€â”€
function morStep(n) {
  [1,2,3].forEach(function(i){
    document.getElementById('mor-step'+i).style.display = i===n ? 'block' : 'none';
    var si = document.getElementById('ms'+i);
    si.classList.remove('active','done');
    if (i < n) si.classList.add('done');
    else if (i === n) si.classList.add('active');
  });
  document.getElementById('moradorStepBadge').textContent = 'Etapa '+n+' de 3';
  document.getElementById('pgBar').style.width = (n/3*100)+'%';
  window.scrollTo(0,0);
}
function finishMorador() {
  showToast('Cadastro de morador realizado com sucesso! ðŸ¡','success');
  setTimeout(function(){ goTo('home'); morStep(1); },1800);
}

// â”€â”€ TYPE CARDS â”€â”€
function selType(el) {
  document.querySelectorAll('.type-card').forEach(function(c){ c.classList.remove('selected'); });
  el.classList.add('selected');
}

// â”€â”€ CAT BUTTONS â”€â”€
function selCat(el, val) {
  document.querySelectorAll('.cat-btn').forEach(function(b){ b.classList.remove('selected'); });
  el.classList.add('selected');
  document.getElementById('pr-servico').value = val;
}

// â”€â”€ EQUIPE â”€â”€
function addEquipe() {
  var nome   = document.getElementById('eq-nome').value.trim();
  var email  = document.getElementById('eq-email').value.trim();
  var tel    = document.getElementById('eq-tel').value.trim();
  var cargo  = document.getElementById('eq-cargo').value;
  var turno  = document.getElementById('eq-turno').value;
  var status = document.getElementById('eq-status').value;

  if (!nome || !email || !tel || !cargo || !turno || !status) {
    showAlert('alertEquipe','Preencha todos os campos obrigatÃ³rios.','error'); return;
  }
  equipe.push({ id: equipe.length+1, nome, email, tel, cargo, turno, status });
  ['eq-nome','eq-email','eq-tel'].forEach(function(id){ document.getElementById(id).value=''; });
  ['eq-cargo','eq-turno','eq-status'].forEach(function(id){ document.getElementById(id).value=''; });
  showToast('Membro cadastrado com sucesso! ðŸ‘¥','success');
  goTo('lista-equipe');
}

function renderEquipe() {
  var tb = document.getElementById('tbEquipe');
  if (!equipe.length) {
    tb.innerHTML = '<tr><td colspan="8"><div class="empty-state"><span class="icon">ðŸ‘¥</span><h3>Nenhum membro cadastrado</h3><p>Clique em "Novo cadastro" para adicionar.</p></div></td></tr>';
    return;
  }
  tb.innerHTML = equipe.map(function(m){
    var badge = m.status==='Ativo'
      ? '<span class="badge badge-success">â— Ativo</span>'
      : '<span class="badge badge-error">â— Inativo</span>';
    return '<tr><td class="td-muted">#'+m.id+'</td><td><strong>'+esc(m.nome)+'</strong></td><td class="td-muted">'+esc(m.email)+'</td><td>'+esc(m.tel)+'</td><td><span class="badge badge-navy">'+esc(m.cargo)+'</span></td><td>'+esc(m.turno)+'</td><td>'+badge+'</td><td><div class="td-actions"><button class="btn btn-warn" onclick="editarEquipe('+m.id+')">Editar</button><button class="btn btn-danger" onclick="excluirEquipe('+m.id+')">Excluir</button></div></td></tr>';
  }).join('');
}

function editarEquipe(id) {
  var m = equipe.find(function(x){ return x.id===id; });
  if (!m) return;
  editTarget = id; editType = 'equipe';
  document.getElementById('modalTitle').textContent = 'Editar Membro â€” '+m.nome;
  document.getElementById('modalBody').innerHTML = `
    <div class="form-grid">
      <div class="field col-span-2"><label>Nome</label><input type="text" id="me-nome" value="${esc(m.nome)}"></div>
      <div class="field"><label>E-mail</label><input type="email" id="me-email" value="${esc(m.email)}"></div>
      <div class="field"><label>Telefone</label><input type="tel" id="me-tel" value="${esc(m.tel)}" oninput="maskTel(this)"></div>
      <div class="field"><label>Cargo</label>
        <select id="me-cargo">${['Analista','Suporte','Supervisor','Administrador'].map(function(c){ return '<option'+(c===m.cargo?' selected':'')+'>'+c+'</option>'; }).join('')}</select>
      </div>
      <div class="field"><label>Turno</label>
        <select id="me-turno">${['ManhÃ£','Tarde','Noite','Integral'].map(function(t){ return '<option'+(t===m.turno?' selected':'')+'>'+t+'</option>'; }).join('')}</select>
      </div>
      <div class="field"><label>Status</label>
        <select id="me-status">${['Ativo','Inativo'].map(function(s){ return '<option'+(s===m.status?' selected':'')+'>'+s+'</option>'; }).join('')}</select>
      </div>
    </div>
    <div class="actions-row"><button class="btn btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn btn-primary" onclick="salvarEquipe()">Salvar âœ“</button></div>`;
  openModal();
}

function salvarEquipe() {
  var m = equipe.find(function(x){ return x.id===editTarget; });
  m.nome   = document.getElementById('me-nome').value.trim() || m.nome;
  m.email  = document.getElementById('me-email').value.trim() || m.email;
  m.tel    = document.getElementById('me-tel').value.trim() || m.tel;
  m.cargo  = document.getElementById('me-cargo').value;
  m.turno  = document.getElementById('me-turno').value;
  m.status = document.getElementById('me-status').value;
  closeModal();
  showToast('AlteraÃ§Ãµes salvas! âœ“','success');
  renderEquipe();
}

function excluirEquipe(id) {
  if (!confirm('Excluir este membro?')) return;
  equipe = equipe.filter(function(x){ return x.id!==id; });
  renderEquipe();
  showToast('Membro removido.','error');
}

// â”€â”€ PRESTADOR â”€â”€
function addPrestador() {
  var nome   = document.getElementById('pr-nome').value.trim();
  var servico= document.getElementById('pr-servico').value.trim();
  var tel    = document.getElementById('pr-tel').value.trim();
  var email  = document.getElementById('pr-email').value.trim();
  var nasc   = document.getElementById('pr-nasc').value;
  var end    = document.getElementById('pr-end').value.trim();

  if (!nome || !servico || !tel || !email || !end) {
    showAlert('alertPrest','Preencha todos os campos obrigatÃ³rios.','error'); return;
  }
  prestadores.push({ id: prestadores.length+1, nome, servico, tel, email, nasc, end });
  ['pr-nome','pr-servico','pr-tel','pr-email','pr-nasc','pr-end','pr-desc','pr-senha','pr-senha2'].forEach(function(id){ document.getElementById(id).value=''; });
  document.querySelectorAll('.cat-btn').forEach(function(b){ b.classList.remove('selected'); });
  showToast('Prestador cadastrado com sucesso! ðŸ”§','success');
  goTo('lista-prestador');
}

function renderPrestadores() {
  var tb = document.getElementById('tbPrestador');
  if (!prestadores.length) {
    tb.innerHTML = '<tr><td colspan="8"><div class="empty-state"><span class="icon">ðŸ”§</span><h3>Nenhum prestador cadastrado</h3><p>Clique em "Cadastrar prestador" para adicionar.</p></div></td></tr>';
    return;
  }
  tb.innerHTML = prestadores.map(function(p){
    return '<tr><td class="td-muted">#'+p.id+'</td><td><strong>'+esc(p.nome)+'</strong></td><td><span class="badge badge-success">'+esc(p.servico)+'</span></td><td>'+esc(p.tel)+'</td><td class="td-muted">'+esc(p.email)+'</td><td>'+esc(p.nasc||'â€”')+'</td><td class="td-muted" style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="'+esc(p.end)+'">'+esc(p.end)+'</td><td><div class="td-actions"><button class="btn btn-warn" onclick="editarPrest('+p.id+')">Editar</button><button class="btn btn-danger" onclick="excluirPrest('+p.id+')">Excluir</button></div></td></tr>';
  }).join('');
}

function editarPrest(id) {
  var p = prestadores.find(function(x){ return x.id===id; });
  if (!p) return;
  editTarget = id; editType = 'prestador';
  document.getElementById('modalTitle').textContent = 'Editar Prestador â€” '+p.nome;
  document.getElementById('modalBody').innerHTML = `
    <div class="form-grid">
      <div class="field col-span-2"><label>Nome</label><input type="text" id="mp-nome" value="${esc(p.nome)}"></div>
      <div class="field"><label>Tipo de serviÃ§o</label><input type="text" id="mp-servico" value="${esc(p.servico)}"></div>
      <div class="field"><label>Telefone</label><input type="tel" id="mp-tel" value="${esc(p.tel)}" oninput="maskTel(this)"></div>
      <div class="field"><label>E-mail</label><input type="email" id="mp-email" value="${esc(p.email)}"></div>
      <div class="field"><label>Nascimento</label><input type="date" id="mp-nasc" value="${p.nasc||''}"></div>
      <div class="field col-span-2"><label>EndereÃ§o</label><input type="text" id="mp-end" value="${esc(p.end)}"></div>
    </div>
    <div class="actions-row"><button class="btn btn-ghost" onclick="closeModal()">Cancelar</button><button class="btn btn-primary" onclick="salvarPrest()">Salvar âœ“</button></div>`;
  openModal();
}

function salvarPrest() {
  var p = prestadores.find(function(x){ return x.id===editTarget; });
  p.nome    = document.getElementById('mp-nome').value.trim() || p.nome;
  p.servico = document.getElementById('mp-servico').value.trim() || p.servico;
  p.tel     = document.getElementById('mp-tel').value.trim() || p.tel;
  p.email   = document.getElementById('mp-email').value.trim() || p.email;
  p.nasc    = document.getElementById('mp-nasc').value;
  p.end     = document.getElementById('mp-end').value.trim() || p.end;
  closeModal();
  showToast('AlteraÃ§Ãµes salvas! âœ“','success');
  renderPrestadores();
}

function excluirPrest(id) {
  if (!confirm('Excluir este prestador?')) return;
  prestadores = prestadores.filter(function(x){ return x.id!==id; });
  renderPrestadores();
  showToast('Prestador removido.','error');
}

// â”€â”€ MODAL â”€â”€
function openModal()  { document.getElementById('modalOverlay').classList.add('open'); }
function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }

// â”€â”€ TOAST â”€â”€
function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast show '+(type||'');
  setTimeout(function(){ t.className='toast'; }, 3000);
}

// â”€â”€ ALERT â”€â”€
function showAlert(id, msg, type) {
  var el = document.getElementById(id);
  el.textContent = msg;
  el.className   = 'alert show alert-'+type;
  setTimeout(function(){ el.className='alert'; }, 4000);
}

// â”€â”€ ESC â”€â”€
function esc(str) {
  return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
