<?php
require_once("/web/libs/php/includes/servidores_catracas.inc");
require_once("/web/libs/php/classes/BD/BD.php");
require_once("/web/libs/php/includes/templates.inc");
require_once('/web/libs/php/includes/browser.inc');
require_once('/web/libs/php/classes/Autentica/Autentica.class.php');
require_once("include/controle.php");

// Definindo o template a ser usado
$tit_template = "Universidade Anhembi Morumbi";

// Instancia a classe de conexão com o banco e chama a tela de autenticação.
$bd = new BD('banner');
$auth = new Autentica($bd);
$lgn_usr = $auth->pidm;

$arr_bd_sqlserver = $arr_bd_catracas;

// Conectando com o banco SQL Server
$bd_ss = new BD(current($arr_bd_sqlserver));

$ret = f_acesso($lgn_usr);

if (!$ret['TP_ACESSO'][0]) {
	$msg = "Área restrita.";
	$arq_miolo_template="template/mens.inc";
	include($template);
	exit;
}

$acao = $_REQUEST['acao'];

if ($acao == "pesquisando") {
	$f_tipo = $_REQUEST['f_tipo'];

	if ($f_tipo == 1)
		$v_tipo = "1";
	else
		$v_tipo = "2,3";

	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];

	$v_RA = str_replace("'", "''", $f_RA);
	$v_nome = str_replace("'", "''", $f_nome);

	if ($v_tipo == 2) {
		$f_tipo_F = "checked";
	} else {
		$f_tipo_A = "checked";
	}

	if ( ($v_RA != "") || ($v_nome != "") )	{
		if ( ($v_RA != "") && ($v_nome != "") ) {
			$sql_w = "p.pes_numero like '%".$v_RA."' and p.pes_nome like upper('".$v_nome."%')";
		} elseif ($v_RA != "") {
			$sql_w = "p.pes_numero like '%".$v_RA."'";
		} elseif ($v_nome != "") {
			$sql_w = "p.pes_nome like upper('".$v_nome."%')";
		}

		$sql = "select p.pes_numero PES_NUMERO,
					right(p.pes_numero,8) RA,
					upper(p.pes_nome) NOME,
					upper(e.est_descricao) TIPO,
					p.pessit_numero,
					case
						when max(crpes_datasaida) < getdate() then 'Bloqueado'
						else 'Liberado'
					end SITUACAO
				from pessoas p, estrutura e, cred_pessoas cp
				where p.est_numero = e.est_numero
				and p.pes_numero = cp.pes_numero
				and p.est_numero in (".$v_tipo.")
				and ".$sql_w."
				group by p.pes_numero,
					right(p.pes_numero,8),
					p.pes_nome,
					e.est_descricao,
					p.pessit_numero
				order by p.pes_nome";
		$num_pesq = $bd_ss->executaSelect($sql, $res_pesq);

	} else {
		$erro = "Por favor, informe pelo menos um dos dados solicitados.";
	}
	$arq_miolo_template = "template/bloquear_liberar_full.inc";

} elseif ($acao == "alt_sit") {
	$pes_numero = $_REQUEST['pes_numero'];
	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];
	$f_tipo = $_REQUEST['f_tipo'];

	$sql = "select p.pes_numero PES_NUMERO,
				right(p.pes_numero,8) RA,
				upper(p.pes_nome) NOME,
				upper(e.est_descricao) TIPO,
				case
					when max(crpes_datasaida) < getdate() then 'Bloqueado'
					else 'Liberado'
				end SITUACAO
			from pessoas p, estrutura e, cred_pessoas cp
			where p.est_numero = e.est_numero
			and p.pes_numero = cp.pes_numero
			and p.pes_numero = ".$pes_numero."
			group by p.pes_numero,
				right(p.pes_numero,8),
				p.pes_nome,
				e.est_descricao--, p.pessit_numero";
	$num = $bd_ss->executaSelect($sql, $res);

	$sql = "select cred_numero,
				case
					when crpes_datasaida >= getdate() then 'Liberado'
					else 'Bloqueado'
				end SITUACAO
			from cred_pessoas cp
			where cp.pes_numero = ".$pes_numero."
			order by crpes_datasaida desc";
	$num_cartoes = $bd_ss->executaSelect($sql, $res_cartoes);

	$arq_miolo_template = "template/alt_sit_full.inc";

} elseif ($acao == "gravando_sit") {
	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];
	$f_tipo = $_REQUEST['f_tipo'];

	$bd_ss->free();

	// grava em cada um dos servidores SQL Server
	foreach ($arr_bd_sqlserver as $chave=>$valor) {
		// Conectando com o banco SQL Server
		$bd_ss_c = new BD($arr_bd_sqlserver[$chave]);

		if ($_POST["bloq_lib"] == "lib") {
			$sql = "select cred_numero,
						case
							when crpes_datasaida >= getdate() then 'Liberado'
							else 'Bloqueado'
						end SITUACAO
					from cred_pessoas cp
					where cp.pes_numero = ".$_POST["pes_numero"]."
					order by crpes_datasaida desc";
			$num_cartoes = $bd_ss_c->executaSelect($sql, $res_cartoes);

			// se nao tiver nenhum cartao liberado, libera
			if ($res_cartoes[SITUACAO][0] != "Liberado") {
				// grava os acessos
				$sql_ss =  "select GRP_NUMERO from grupos
							order by GRP_NUMERO";
				$num_ss_grp = $bd_ss_c->executaSelect($sql_ss, $ret_ss_grp);

				$sql_ss =  "select ARE_NUMERO from areas
							order by ARE_NUMERO";
				$num_ss_are = $bd_ss_c->executaSelect($sql_ss, $ret_ss_are);

				for ($g=0; $g<$num_ss_grp; $g++) {
					for ($a=0; $a<$num_ss_are; $a++) {
						$sql_ss =  "select cred_numero from cred_acesso
									where cred_numero = '".$_POST["cred_numero"]."'
									and grp_numero = ".$ret_ss_grp[GRP_NUMERO][$g]."
									and are_numero = ".$ret_ss_are[ARE_NUMERO][$a];
						$num_ss_acesso = $bd_ss_c->executaSelect($sql_ss, $ret_ss_acesso);

						if ($num_ss_acesso == 0) {
							$sql_ss =  "insert into cred_acesso (cred_numero, grp_numero, are_numero, cracti_numero, crac_qtdtotalacessos)
										values ('".$_POST["cred_numero"]."', ".$ret_ss_grp[GRP_NUMERO][$g].", ".$ret_ss_are[ARE_NUMERO][$a].", 1, 999)";
							$bd_ss_c->executaQuery($sql_ss);
						}
					}
				}



				// grava pessit_numero = 2 => liberado
				$sql = "update pessoas
						set pessit_numero = (select pessit_numero from pessoa_situacao where pessit_descricao = 'Liberada')
						where pes_numero = ".$_POST["pes_numero"];
				$bd_ss_c->executaQuery($sql);

				// libera o cartao
				$sql = "update cred_pessoas
						set crpes_datasaida = dateadd(year, 100, getdate())
						where cred_numero = '".$_POST["cred_numero"]."'
						and pes_numero = ".$_POST["pes_numero"];
				$bd_ss_c->executaQuery($sql);
			}
		} else {
			// bloquear (pela data) essas credenciais
			$sql = "update cred_pessoas
					set crpes_datasaida = getdate()
					where cred_numero = '".$_POST["cred_numero"]."'
					and pes_numero = ".$_POST["pes_numero"];
			$bd_ss_c->executaQuery($sql);

			// gravar cred_areanomomento = 0 (nenhuma)
			$sql = "update credenciais
					set cred_areanomomento = 0
					where cred_numero = '".$_POST["cred_numero"]."'";
			$bd_ss_c->executaQuery($sql);
		}

		$bd_ss_c->commit();
		$bd_ss_c->free();
	}

	header('Location: bloquear_liberar_full.php?acao=pesquisando&f_tipo='.$f_tipo.'&f_RA='.$f_RA.'&f_nome='.$f_nome);
	exit;

} else {
	$f_tipo_A = "checked";
	$arq_miolo_template = "template/bloquear_liberar_full.inc";
}

require($template);

$bd->free();
$bd_ss->free();

?>