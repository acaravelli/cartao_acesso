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

// Conectando com o banco Oracle
if (BANNER_INSTANCIA == 'PAXD') {
	$bd_rafael = new BD($base);
} else {
	$bd_rafael = $bd;
}

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
	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];

	$v_RA = str_replace("'", "''", $f_RA);
	$v_nome = str_replace("'", "''", $f_nome);

	if ( ($v_RA != "") || ($v_nome != "") )	{
		if ( ($v_RA != "") && ($v_nome != "") ) {
			$sql_w = "and (iden.spriden_id = '{$v_RA}' or iden.spriden_id = lpad('{$v_RA}', 8, 0)) and baninst1.retira_acentos(spriden_first_name|| ' ' || spriden_last_name) like baninst1.retira_acentos(upper('".$v_nome."%'))";
		} elseif ($v_RA != "") {
			$sql_w = "and (iden.spriden_id = '{$v_RA}' or iden.spriden_id = lpad('{$v_RA}', 8, 0))";
		} elseif ($v_nome != "") {
			$sql_w = "and baninst1.retira_acentos(spriden_first_name || ' ' || spriden_last_name) like baninst1.retira_acentos(upper('{$v_nome}%'))";
		}

		$sql = "SELECT X.*
				FROM (SELECT DISTINCT SUBSTR(SWRENRL_TERM_CODE, 1, 5) || '|' ||
										(CASE TO_NUMBER(SUBSTR(SWRENRL_TERM_CODE, 6, 1))
										WHEN 3 THEN
											6
										WHEN 2 THEN
											5
										WHEN 1 THEN
											4
										WHEN 0 THEN
											3
										WHEN 5 THEN
											2
										WHEN 6 THEN
											1
										END) PRIO,
										ENRL.SWRENRL_PIDM PIDM,
										IDEN.SPRIDEN_ID RA,
										SPRIDEN_FIRST_NAME || ' ' || SPRIDEN_LAST_NAME NOME,
										ENRL.SWRENRL_LEVL_DESC TIP_CUR,
										ENRL.SWRENRL_PROGRAM PROGRAM,
										ENRL.SWRENRL_PROGRAM_DESC CURSO,
										ENRL.SWRENRL_SERIE SER,
										SUBSTR(ENRL.SWRENRL_CAMP_CODE, 1, 2) CPS,
										ENRL.SWRENRL_TERM_CODE PLT_COD
						FROM SWRENRL ENRL,
							SATURN.SPRIDEN IDEN,
							(SELECT TERM.STVTERM_CODE FROM SATURN.STVTERM TERM) T
						WHERE ENRL.SWRENRL_STATUS_UAM = 'ANDAMENTO'
						AND IDEN.SPRIDEN_PIDM = ENRL.SWRENRL_PIDM
						AND IDEN.SPRIDEN_CHANGE_IND IS NULL
						AND ENRL.SWRENRL_SERIE IS NOT NULL
						AND ENRL.SWRENRL_TERM_CODE = T.STVTERM_CODE {$sql_w}
						ORDER BY 1 DESC, 3) X
				WHERE ROWNUM = 1";

		$num_pesq = $bd->executaSelect($sql, $res_pesq);
	} else {
		$erro = "Por favor, informe pelo menos um dos dados solicitados.";
	}
	$arq_miolo_template = "template/main.inc";

} elseif ($acao == "alt_cps" || $acao == "entrega" || $acao == "reimp") {
	$ra = $_REQUEST['ra'];
	$pidm = $_REQUEST['pidm'];
	$term = $_REQUEST['term'];
	$program = $_REQUEST['program'];

	$fl = $_REQUEST['fl'];
	$cd = $_REQUEST['cd'];
	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];

	$sql = "select distinct
					iden.spriden_id ra,
					spriden_first_name|| ' ' || spriden_last_name nome,
					enrl.swrenrl_levl_desc tip_cur,
					enrl.swrenrl_program_desc curso,
					enrl.swrenrl_serie ser,
					substr(enrl.swrenrl_camp_code, 1, 2) cps,
					enrl.swrenrl_term_code plt_cod
			from swrenrl enrl,
				saturn.spriden iden,
				(select term.stvterm_code
				from saturn.stvterm term
				) t
			where enrl.swrenrl_status_uam = 'ANDAMENTO'
				and iden.spriden_pidm = enrl.swrenrl_pidm
				and iden.spriden_change_ind is null
			and enrl.swrenrl_serie is not null
			and enrl.swrenrl_term_code = t.stvterm_code
			and iden.spriden_id = '{$ra}'
			and enrl.swrenrl_pidm = {$pidm}
			and enrl.swrenrl_term_code = '{$term}'
			and enrl.swrenrl_program = '{$program}'";
	$num = $bd->executaSelect($sql, $res);

	if ( ($acao == "alt_cps" && $fl == "" && $cd == 0) || ($acao == "reimp" && $fl == "ss" && $cd == 0) ) {
		// verifica se está na fila de impressão
		$sql = "select *
			from catracas.impressao_alunos i
			where i.RA = " . $ra . "
			and i.curso = '" . $res[CURSO][0] . "'";
		$num_fila = $bd_rafael->executaSelect($sql, $ret_fila);

		if ($num_fila > 0) {
			$cd = $ret_fila['COD'][0];
		}
	} else {
		// entrega - verifica se está na fila de impressão
		$sql = "select *
			from catracas.impressao_alunos i
			where i.RA = {$ra}
			and i.cod = {$cd}";
		$num_fila = $bd_rafael->executaSelect($sql, $ret_fila);
	}

	if ($num_fila > 0) {
		// pega o campus onde será impresso (impresso=0) ou onde foi impresso - caso o cron ainda nao tenha rodado (impresso=1)
		switch ($ret_fila[CAMPUS][0]) {
			case 'VO':
			case 'CE':
			case 'MO':
			case 'VA':
			case 'PA':
			case 'PA2':
				$cps = $ret_fila[CAMPUS][0];
				break;
			default:
				$cps = 'VO';
		}
	} else {
		// verifica se está na fila de impressão log (já foi impresso)
		$sql = "select *
			from catracas.impressao_alunos_log i
			where i.RA = ".$ra."
			and i.cod = ".$cd."
			order by i.dat_imp desc";
		$num_fila_log = $bd_rafael->executaSelect($sql, $ret_fila_log);

		// se está na fila de impressão log, pega o campus onde foi impresso
		if ($num_fila_log > 0) {
			switch ($ret_fila_log[CAMPUS][0]) {
				case 'VO':
				case 'CE':
				case 'MO':
				case 'VA':
				case 'PA':
				case 'PA2':
					$cps = $ret_fila_log[CAMPUS][0];
					break;
				default:
					$cps = 'VO';
			}
		} else {
			// verifica se já está no SQL Server

			$sql =  "select cred_numero, --crpes_datasaida,
					case
						when crpes_datasaida >= getdate() then 'Liberado'
						else 'Bloqueado'
					end SITUACAO
					from cred_pessoas
					where pes_numero = '1' + replicate(0,11-len(".$ra.")) + '".$ra."'
					order by crpes_datasaida desc";
			$num_ss = $bd_ss->executaSelect($sql, $ret_ss);

			if ($num_ss > 0) {
				$sql = "select ca.dat_acesso max_dat_acesso
								from catracas.acessos_aln ca
								where ca.mtr_num = ".$ra."
								order by ca.dat_acesso desc";
				$num_acessos = $bd->executaSelect($sql, $ret_acessos);

				if ($num_acessos > 0) {
					$ultimo_acesso = "O último acesso deste aluno foi em ".$ret_acessos['MAX_DAT_ACESSO'][0].".";
				} else {
					$ultimo_acesso = "Este aluno não possui marcação de passagem nas catracas.";
				}
			}

			// se não está em nenhum caso, pega o campus onde está matriculado
				switch ($res[CPS][0]) {
					case 'VO':
					case 'CE':
					case 'MO':
					case 'VA':
					case 'PA':
					case 'PA2':
						$cps = $res[CPS][0];
						break;
					default:
						$cps = 'VO';
				}
		}
	}
	$arq_miolo_template = "template/alt_cps.inc";

} elseif ($acao == "gravando_cps" || $acao == "gravando_entr" || $acao == "gravando_reimp") {
	$ra = $_REQUEST['ra'];
	$pidm = $_REQUEST['pidm'];
	$term = $_REQUEST['term'];
	$program = $_REQUEST['program'];

	$fila = $_REQUEST['fila'];
	$cd = $_REQUEST['cd'];
	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];

	$f_cps = $_REQUEST['f_cps'];
	$f_mtv = $_REQUEST['f_mtv'];
	$txt_obs = $_REQUEST['txt_obs'];
	$nome_impresso = $_REQUEST['nome_impresso'];

	// marca carteirinha como entregue
	if ($acao == "gravando_entr") {
		// verifica se está na fila de impressão
		$sql = "select *
			from catracas.impressao_alunos i
			where i.RA = ".$ra."
			and i.cod = ".$cd;
		$num_fila = $bd_rafael->executaSelect($sql, $ret_fila);

		if ($num_fila > 0 && $fila == "fila_imp") {
			$insere = " update catracas.impressao_alunos i
					set i.dat_entrega = sysdate,
					i.usr_entrega = '".$lgn_usr."'
					where i.ra = ".$ra."
					and i.cod = ".$cd;
			$bd_rafael->executaQuery($insere);
			$bd_rafael->commit();
		} else {
			// verifica se está na fila de impressão log (já foi impresso)
			$sql = "select *
				from catracas.impressao_alunos_log i
				where i.RA = ".$ra."
				and i.cod = ".$cd;
			$num_fila_log = $bd_rafael->executaSelect($sql, $ret_fila_log);

			if ($num_fila_log > 0 && $fila == "fila_log") {
				$insere = " update catracas.impressao_alunos_log i
						set i.dat_entrega = sysdate,
						i.usr_entrega = '".$lgn_usr."'
						where i.ra = ".$ra."
						and i.cod = ".$cd;
				$bd_rafael->executaQuery($insere);
				$bd_rafael->commit();
			}
		}

	// altera o campus ou insere reimpressão
	} elseif ($acao == "gravando_cps" || $acao == "gravando_reimp") {
		// verifica se está na fila de impressão
		$sql = "select *
			from catracas.impressao_alunos i
			where i.RA = ".$ra."
			and i.cod = ".$cd;
		$num_fila = $bd_rafael->executaSelect($sql, $ret_fila);

		if ($acao == "gravando_cps" && $num_fila > 0 && $fila == "fila_imp") {
			$insere = " update catracas.impressao_alunos i
						set i.campus = '".$f_cps."'
						where i.ra = ".$ra."
						and i.cod = ".$cd;
			$bd_rafael->executaQuery($insere);
			$bd_rafael->commit();

		} elseif ($ret['TP_ACESSO'][0] == "LIDER") {
			// pega dados atualizados (e não mais da tabela impressao_alunos_log)
			$sql = "select distinct
					iden.spriden_id ra,
					spriden_first_name|| ' ' || spriden_last_name nome,
					enrl.swrenrl_levl_desc tip_cur,
					enrl.swrenrl_program_desc curso,
					enrl.swrenrl_serie ser,
					case
					when (substr(enrl.swrenrl_camp_code, 1, 2) not in ('VO','VA','PA','MO','CE','PA2')) then 'VO'
					else substr(enrl.swrenrl_camp_code, 1, 2)
					end cps,
					enrl.swrenrl_term_code plt_cod,
					case
						when (enrl.swrenrl_serie = 'IN') then (select x.x  ||'/'|| to_char(sysdate, 'yyyy') validade from (select case when to_char(sysdate, 'mm') between '01' and '06' then 'Jun' else 'Dez' end x from dual) x)
						when substr(enrl.swrenrl_term_code, 5, 1) = '1' then
							case
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 0) then 'Jun/'||to_char(sysdate,'yyyy')
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 1) then 'Dez/'||to_char(sysdate,'yyyy')
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 2) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 3) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 4) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 5) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 6) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 7) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 8) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 9) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 10) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 11) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 12) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+6)
								else 'Jun/'||to_char(sysdate,'yyyy')
							end
						else
							case
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 0) then 'Dez/'||to_char(sysdate,'yyyy')
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 1) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 2) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 3) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 4) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 5) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 6) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 7) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 8) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 9) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 10) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 11) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+6)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 12) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+6)
								else 'Dez/'||to_char(sysdate,'yyyy')
						end
					end VALIDADE
				from swrenrl enrl,
					saturn.spriden iden,
					(select term.stvterm_code
					from saturn.stvterm term
					) t
				where enrl.swrenrl_status_uam = 'ANDAMENTO'
					and iden.spriden_pidm = enrl.swrenrl_pidm
					and iden.spriden_change_ind is null
				and enrl.swrenrl_serie is not null
				and enrl.swrenrl_term_code = t.stvterm_code
				and iden.spriden_id = '".$ra."'
				and enrl.swrenrl_pidm = ".$pidm."
				and enrl.swrenrl_term_code = '".$term."'
				and enrl.swrenrl_program = '".$program."'";
			$num = $bd->executaSelect($sql, $res);

			if ($acao == "gravando_reimp" && $fila == "fila_imp" && $cd > 0) {

			} elseif ($acao == "gravando_reimp" && $fila == "fila_log" && $cd > 0) {
				$sql = "select *
						from catracas.impressao_alunos_log i
						where i.RA = ".$ra."
						and i.cod = ".$cd;
				$num_fila = $bd_rafael->executaSelect($sql, $ret_fila);

				$b = array('ra' => $ra);
				$bd_rafael->executaSelectComBind("select count(1) cnt from catracas.impressao_alunos where ra = :ra and id is null", $ret1, $b);

				if ($ret1['CNT'][0]) {
					$bd_rafael->executaQueryComBind("delete from catracas.impressao_alunos where ra = :ra and id is null", $b);
				}

				$insere = " insert into catracas.impressao_alunos
							(cod, ra, nome, curso, validade, campus, id, impresso, rqt_cod, dat_imp, dat_entrega, usr_ics, dat_ics, mtv_reimpressao, obs_reimpressao)
							values (
							catracas.impressao_alunos_seq.nextval, :ra, :nome, :curso, :validade, :campus, '', 0, :rqt_cod, null, null, :userid, sysdate, :mtv_reimpressao, :obs_reimpressao
							)";

				$b = array(
					'ra' => $ra,
					'nome' => $bd_rafael->arrumaString($res['NOME'][0]),
					'curso' => $res['CURSO'][0],
					'validade' => $res['VALIDADE'][0],
					'campus' => $f_cps,
					'rqt_cod' => $ret_fila['RQT_COD'][0],
					'userid' => $lgn_usr,
					'mtv_reimpressao' => $f_mtv,
					'obs_reimpressao' => $txt_obs
				);

				$bd_rafael->executaQueryComBind($insere, $b);
				$bd_rafael->commit();

			} elseif ( ($fila == "" && $cd == 0) || ($fila == "fila_ss" && $cd == 0) ) {
				$b = array('ra' => $ra);
				$bd_rafael->executaSelectComBind("select count(1) cnt from catracas.impressao_alunos where ra = :ra and id is null", $ret1, $b);

				if ($ret1['CNT'][0]) {
					$bd_rafael->executaQueryComBind("delete from catracas.impressao_alunos where ra = :ra and id is null", $b);
				}

				// insere na fila de impressão (apenas lideres (acesso total))
				$insere = " insert into catracas.impressao_alunos
							(cod, ra, nome, curso, validade, campus, id, impresso, rqt_cod, dat_imp, dat_entrega, usr_ics, dat_ics, mtv_reimpressao, obs_reimpressao)
							values (
							catracas.impressao_alunos_seq.nextval, ".$ra.", '".$bd_rafael->arrumaString($res['NOME'][0])."', '".$res['CURSO'][0]."', '".$res['VALIDADE'][0]."', '".$f_cps."', '', 0, 0, null, null, '".$lgn_usr."', sysdate, '".$f_mtv."', '".$txt_obs."'
							)";
				$bd_rafael->executaQuery($insere);
				$bd_rafael->commit();
			}
		}
	}
	header('Location: index.php?acao=pesquisando&f_RA='.$f_RA.'&f_nome='.$f_nome);
	exit;

} elseif ($acao == "gera_fila") {
	$f_RA = $_REQUEST['f_RA'];
	$f_nome = $_REQUEST['f_nome'];

	// lideres e backs
	if ($ret['TP_ACESSO'][0] == "LIDER" || $ret['TP_ACESSO'][0] == "BACK") {
		$n = count($_POST['chk_fila']);
		for($i = 0; $i < $n; $i++) {
			//$chk_fila = "07112046|7112046|201110|PSIC";

			$chk_fila = $_POST['chk_fila'][$i];
			$ra = substr($chk_fila, 0, strpos($chk_fila, "|"));

			$chk_fila = strstr($chk_fila, "|");
			$chk_fila = substr($chk_fila, 1, strlen($chk_fila)-1);
			$pidm = substr($chk_fila, 0, strpos($chk_fila, "|"));

			$chk_fila = strstr($chk_fila, "|");
			$chk_fila = substr($chk_fila, 1, strlen($chk_fila)-1);
			$term = substr($chk_fila, 0, strpos($chk_fila, "|"));

			$chk_fila = strstr($chk_fila, "|");
			$chk_fila = substr($chk_fila, 1, strlen($chk_fila)-1);
			$program = $chk_fila;

			$sql = "select distinct
					iden.spriden_id ra,
					spriden_first_name|| ' ' || spriden_last_name nome,
					enrl.swrenrl_levl_desc tip_cur,
					enrl.swrenrl_program_desc curso,
					enrl.swrenrl_serie ser,
					case
					when (substr(enrl.swrenrl_camp_code, 1, 2) not in ('VO','VA','PA','MO','CE','PA2')) then 'VO'
					else substr(enrl.swrenrl_camp_code, 1, 2)
					end cps,
					enrl.swrenrl_term_code plt_cod,
					case
						when (enrl.swrenrl_serie = 'IN') then (select x.x  ||'/'|| to_char(sysdate, 'yyyy') validade from (select case when to_char(sysdate, 'mm') between '01' and '06' then 'Jun' else 'Dez' end x from dual) x)
						when substr(enrl.swrenrl_term_code, 5, 1) = '1' then
							case
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 0) then 'Jun/'||to_char(sysdate,'yyyy')
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 1) then 'Dez/'||to_char(sysdate,'yyyy')
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 2) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 3) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 4) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 5) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 6) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 7) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 8) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 9) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 10) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 11) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 12) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+6)
								else 'Jun/'||to_char(sysdate,'yyyy')
							end
						else
							case
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 0) then 'Dez/'||to_char(sysdate,'yyyy')
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 1) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 2) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+1)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 3) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 4) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+2)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 5) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 6) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+3)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 7) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 8) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+4)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 9) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 10) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+5)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 11) then 'Jun/'||(to_number(to_char(sysdate,'yyyy'))+6)
								when (enrl.swrenrl_dur_sem - enrl.swrenrl_serie = 12) then 'Dez/'||(to_number(to_char(sysdate,'yyyy'))+6)
								else 'Dez/'||to_char(sysdate,'yyyy')
						end
					end VALIDADE
				from swrenrl enrl,
					saturn.spriden iden,
					(select term.stvterm_code
					from saturn.stvterm term
					--where trunc(sysdate) between trunc(term.stvterm_start_date) and trunc(term.stvterm_end_date)
					) t
				where enrl.swrenrl_status_uam = 'ANDAMENTO'
					and iden.spriden_pidm = enrl.swrenrl_pidm
						and iden.spriden_change_ind is null
				and enrl.swrenrl_serie is not null
				and enrl.swrenrl_term_code = t.stvterm_code
				and iden.spriden_id = '".$ra."'
				and enrl.swrenrl_pidm = ".$pidm."
				and enrl.swrenrl_term_code = '".$term."'
				and enrl.swrenrl_program = '".$program."'";
			$num = $bd->executaSelect($sql, $res);

			$b = array('ra' => $ra);
			$bd_rafael->executaSelectComBind("select count(1) cnt from catracas.impressao_alunos where ra = :ra and id is null", $ret1, $b);

			if ($ret1['CNT'][0]) {
				$bd_rafael->executaQueryComBind("delete from catracas.impressao_alunos where ra = :ra and id is null", $b);
			}

			$insere = " insert into catracas.impressao_alunos
						(cod, ra, nome, curso, validade, campus, id, impresso, rqt_cod, dat_imp, dat_entrega, usr_ics, dat_ics, mtv_reimpressao, obs_reimpressao)
						values (
						catracas.impressao_alunos_seq.nextval, ".$ra.", '".$bd_rafael->arrumaString($res['NOME'][0])."', '".$res['CURSO'][0]."', '".$res['VALIDADE'][0]."', '".$res['CPS'][0]."', '', 0, 0, null, null, '".$lgn_usr."', sysdate, '".$f_mtv."', '".$txt_obs."'
						)";
			$bd_rafael->executaQuery($insere);
			$bd_rafael->commit();
		}
	}

	header('Location: ./?acao=pesquisando&f_RA='.$f_RA.'&f_nome='.$f_nome);
	exit;

} else {
	$arq_miolo_template = "template/main.inc";
}

require($template);

$bd->free();
