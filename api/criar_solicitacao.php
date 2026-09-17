<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validators.php';
$user=require_auth('morador');$data=input_json();
$categoria=text_field($data,'categoria',120);$endereco=text_field($data,'endereco',255);$descricao=text_field($data,'descricao',3000);$dataDesejada=trim((string)($data['data']??''));
if(!in_array($categoria,['Limpeza pesada','Limpeza diaria','Baba','Cuidador de idosos'],true))json_response(['ok'=>false,'message'=>'Categoria inválida.'],422);
if($dataDesejada!=='') ensure_date($dataDesejada, 'A data desejada');
if($dataDesejada!==''&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$dataDesejada)||$dataDesejada<date('Y-m-d')))json_response(['ok'=>false,'message'=>'A data desejada deve ser hoje ou uma data futura.'],422);
$stmt=db()->prepare('SELECT id FROM moradores WHERE usuario_id=?');$stmt->execute([$user['id']]);$moradorId=$stmt->fetchColumn();if(!$moradorId)json_response(['ok'=>false,'message'=>'Perfil de morador não encontrado.'],404);
$stmt=db()->prepare('INSERT INTO solicitacoes (morador_id,categoria,data_desejada,endereco,descricao) VALUES (?,?,?,?,?)');$stmt->execute([$moradorId,$categoria,$dataDesejada?:null,$endereco,$descricao]);
json_response(['ok'=>true,'id'=>(int)db()->lastInsertId(),'message'=>'Solicitação criada.'],201);
