<?php
require_once("/web/libs/php/includes/servidores_catracas.inc");
require_once("/web/libs/php/classes/BD/BD.php");
require_once("/web/libs/php/includes/templates.inc");
require_once('/web/libs/php/includes/browser.inc');
require_once('/web/libs/php/classes/Autentica/Autentica.class.php');
require_once("include/controle.inc");

// Definindo o template a ser usado
$tit_template = "Universidade Anhembi Morumbi";

// Instancia a classe de conexão com o banco e chama a tela de autenticação.
$bd = new BD('banner');
$auth = new Autentica($bd);
$lgn_usr = $auth->pidm;

// Conectando com o banco Oracle
if (BANNER_INSTANCIA == 'PAXD') {
	$bd_rafael = new BD($base);
} else {
	$bd_rafael = $bd;
}

$ret = f_acesso($lgn_usr);

if (!$ret['TP_ACESSO'][0]) {
	$msg = "Área restrita.";
	$arq_miolo_template="template/mens.inc";
	include($template);
	exit;
}

$acao = $_REQUEST['acao'];

function relatorio($f_data, $f_data_fim = null)
{
	global $bd_rafael;

	$b = array();

	if ($f_data) {
		$f_data = explode('/', $f_data);
		if (count($f_data) != 3 or !checkdate($f_data[1], $f_data[0], $f_data[2])) {
			$f_data = $date_hoje->get('dd/MM/yyyy');
		} else {
			$f_data = implode('/', $f_data);
		}

		$b['data'] = $f_data;

		if ($f_data_fim) {
			$f_data_fim = explode('/', $f_data_fim);

			if (count($f_data_fim) != 3 or !checkdate($f_data_fim[1], $f_data_fim[0], $f_data_fim[2])) {
				$f_data_fim = $date_hoje->get('dd/MM/yyyy');
			} else {
				$f_data_fim = implode('/', $f_data_fim);
			}

			$sql_s1 = null;
			$sql_s2 = ' and trunc(dat_imp) between trunc(to_date(:data, \'dd/mm/yyyy\')) and trunc(to_date(:data_fim, \'dd/mm/yyyy\'))';
			$b['data_fim'] = $f_data_fim;
		} else {
			$sql_s1 = null;
			$sql_s2 = ' and trunc(dat_imp) = to_date(:data, \'dd/mm/yyyy\')';
		}
	}

	if ($sql_s2) {
		$sql = "select ra, nome, id, 'Não' impresso, dat_imp, to_char(dat_imp, 'dd/mm/yyyy hh24:mi') dat_imp_br from catracas.impressao_alunos where 1 = 1
		and impresso = 1
		$sql_s1
		and id is not null
		union
		select ra, nome, id, 'Sim' impresso, dat_imp, to_char(dat_imp, 'dd/mm/yyyy hh24:mi') dat_imp_br from catracas.impressao_alunos_log where 1 = 1
		$sql_s2
		order by dat_imp desc";

		$num_pesq = $bd_rafael->executaSelectComBind($sql, $ret, $b);
	}

	return $ret;
}

$date = new DateTime();
$date_fim = new DateTime();
$date_hoje = new DateTime();
$date_fim_limite = new DateTime();

if ($_REQUEST['b_REL']) {
	$limite = 120;

	$f_data = (isset($_POST['f_data']) and $_POST['f_data']) ? $_POST['f_data'] : null;
	$f_data_fim = (isset($_POST['f_data_fim']) and $_POST['f_data_fim']) ? $_POST['f_data_fim'] : null;

	if ($f_data) {
		$f_data_aux = explode('/', $f_data);
		$date->setDate($f_data_aux[2], $f_data_aux[1], $f_data_aux[0]);
		$date_fim->setDate($f_data_aux[2], $f_data_aux[1], $f_data_aux[0]);
	}

	$f_data = $date;

	$f_data_aux = explode('/', $f_data->format('d/m/Y'));


	$date_fim_limite = new DateTime("{$f_data_aux[2]}-{$f_data_aux[1]}-{$f_data_aux[0]}");
	$date_fim_limite->modify("+ {$limite} day");

	if ($f_data_fim) {
		$f_data_fim_aux = explode('/', $f_data_fim);
		$date_fim->setDate($f_data_fim_aux[2], $f_data_fim_aux[1], $f_data_fim_aux[0]);
		$f_data_fim = $date_fim;

		if (date_diff1($date_fim_limite, $f_data_fim) < 1) {
			$f_data_fim = $date_fim_limite;
		}
	} else {
		for ($i = $limite; $i > 0; $i--) {
			$date_fim->modify("+ {$i} day");

			if (date_diff1($date_fim, $date_hoje) <= 0) {
				break;
			} else {
				$date_fim->modify("- $i day");
			}
		}

		$f_data_fim = $date_fim;
	}

	$f_data = $f_data->format('d/m/Y');
	$f_data_fim = $f_data_fim->format('d/m/Y');

	$ret = relatorio($f_data, $f_data_fim);
	$num_pesq = count($ret['RA']);

	header('Content-Type: text/csv; charset=utf-8');
	header("Content-Disposition: attachment; filename=cartoes_{$f_data}_{$f_data_fim}.csv");
	header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
	header("Pragma: no-cache"); // HTTP 1.0
	header("Expires: 0"); // Proxies

	$outstream = fopen('php://output', 'w');

	fputcsv($outstream, array("RA", "ALUNO", "CARTÃO", "IMPRESSO", "DATA_IMPRESSÃO"), ';');

	for ($i = 0; $i < $num_pesq; $i++){
		fputcsv($outstream, array($ret['RA'][$i], $ret['NOME'][$i], $ret['ID'][$i], $ret['IMPRESSO'][$i], $ret['DAT_IMP_BR'][$i]), ';');
	}

	fclose($outstream);
	die;
} else {
	$f_data = $date->format('d/m/Y');

	$ret = relatorio($f_data);
	$num_pesq = count($ret['RA']);
}

$arq_miolo_template = "template/relatorio.inc";
require($template);

$bd->free();