<STYLE TYPE="text/css">
<!--
	table { border-color: black; border-collapse: collapse; }

	.link {
		color: #336699;
		text-decoration: none;
	}
	.link:active {
		text-decoration: none;
	}
	.link:visited {
		color: #336699;
		text-decoration: none;
	}
	.link:hover {
		text-decoration: underline;
	}

	.link_reimp {
		color: #999999;
		text-decoration: none;
	}
	.link_reimp:active {
		text-decoration: none;
	}
	.link_reimp:visited {
		color: #999999;
		text-decoration: none;
	}
	.link_reimp:hover {
		text-decoration: underline;
	}


-->
</STYLE>

<table width="700" cellpadding="0" cellspacing="0" border="0" align="left">
<tr>
	<td>
	&nbsp;
	</td>
</tr>

<tr>
	<td align="center" class="texto-preto">
		CONSULTA DE ALUNOS
	</td>
</tr>

<tr>
	<td align="left" class="texto-preto-lt">
		<a href="./">[Consultar alunos]</a>
	</td>
</tr>
<tr>
	<td align="left" class="texto-preto-lt">
		<a href="./bloquear_liberar.php">[Bloquear/liberar]</a>
	</td>
</tr>
<tr>
	<td align="left" class="texto-preto-lt">
		<a href="./relatorio.php">[Relatório de Cartões Impressos]</a>
	</td>
</tr>

<tr>
	<td align="left" class="texto-preto-lt">
		<a href="./sair.php">[Sair]</a>
	</td>
</tr>

<tr>
	<td class="texto-alert" align="center">
	<?=$erro?>
	</td>
</tr>
<tr>
	<td>

	<form name="frm" action="<?=pegaURL()?>" method="post">
	<input type="hidden" name="acao" value="pesquisando">

	<table width="100%" cellpadding="0" cellspacing="0" border="0" class="texto-preto-lt">
		<tr>
		<td align="center"><br>
			<table cellpadding="12" cellspacing="0" border="1">
			<tr>
				<td>
				<table cellpadding="2" cellspacing="0" border="0" class="texto-preto-lt">
					<tr>
					<td width="40" align="left">
						RA
					</td>
					<td align="left">
						<input class="texto-preto-lt" type="text" name="f_RA" maxlength="8" size="8" value='<?=$_REQUEST['f_RA']?>'>
					</td>
					</tr>
					<tr>
					<td align="left">
						Nome
					</td>
					<td align="left">
						<input class="texto-preto-lt" type="text" name="f_nome" maxlength="40" size="20"  value='<?= $_REQUEST['f_nome'] ?>'>
					</td>
					</tr>
				</table>
				</td>
			</tr>
			<table>
		</td>
		</tr>
		<tr>
		<td align="middle"><BR>
			<input type="submit" class="texto-preto-lt" value="Ok" name="b_OK">
		</td>
		</tr>
	</table>
	</form>
	</td>
</tr>

<tr>
	<td>
	Esta consulta retorna apenas os alunos em andamento nos períodos letivos atuais.<br>
	Posicione o ponteiro do mouse sobre o nome do aluno para ver a situação da carteirinha dele.<br><br>
	</td>
</tr>

<?
if ($acao == "pesquisando") {
	if ($num_pesq == 0) {
?>
<tr>
	<td class="texto-preto-lt">A pesquisa não retornou resultados.</td>
</tr>
<?
	} else {
?>

<form name="frm_escolhe" action="<?=pegaURL()?>" method="post">
<input type="hidden" name="acao" value="gera_fila">
<input type="hidden" name="f_RA" value='<?=$_REQUEST['f_RA']?>'>
<input type="hidden" name="f_nome" value='<?= $_REQUEST['f_nome'] ?>'>

<tr>
	<td align="center">
	<table cellpadding="2" cellspacing="0" border="1" class="texto-preto-sm">
		<tr>
		<td class="texto-preto3" width="18">
			<input type="checkbox" name="chk_sel_all"
<?
		if ($ret['TP_ACESSO'][0] == "ATENDENTE")
			echo " disabled ";
?>
			onClick="javascript:selectCheckBox(this, document.frm_escolhe);">
		</td>
		<td class="texto-preto3" width="55">ID</td>
		<td class="texto-preto3">NOME</td>
		<td class="texto-preto3" width="95">SEGMENTO</td>
		<td class="texto-preto3">CURSO</td>
		<td class="texto-preto3">SER</td>
		<td class="texto-preto3">CPS</td>
		<td class="texto-preto3">TERM</td>
		</tr>
<?
		for ($i=0; $i < $num_pesq; $i++) {
			$msg_aviso = "";
			$chk_gera = 0;
			$lk_cps = 0;
			$lk_entrega = 0;
			$qfl = "";
			$cod_fila = 0;

			// verifica se está na fila de impressão
			$sql = "select *
				from catracas.impressao_alunos i
				where i.ra = ".$res_pesq['RA'][$i]."
				--and i.impresso = 0
					and i.curso = '" . $res_pesq['CURSO'][$i] . "'";
			$num_fila = $bd_rafael->executaSelect($sql, $ret_fila);

			if ($num_fila > 0) {
				$qfl = "fila_imp";
				$cod_fila = $ret_fila['COD'][0];
				if ($ret_fila['IMPRESSO'][0] == 0) {
					$chk_gera = 0;
					$lk_cps = 1;
					$lk_entrega = 0;
					$msg_aviso = "Já existe uma solicitação deste aluno na fila de impressão do campus ".$ret_fila['CAMPUS'][0].". Caso deseje alterar o campus de impressão da carteirinha, clique aqui.";

				// caso ainda esteja na fila de impressao mas ja tenha sido impressa (cron nao rodou ainda)
				} else {
					$chk_gera = 0;
					$lk_cps = 0;
					// caso ainda não tenha sido entregue
					if ($ret_fila['DAT_ENTREGA'][0] == null) {
						$lk_entrega = 1;
						$msg_aviso = "Já foi impressa uma carteirinha para este aluno no campus ".$ret_fila['CAMPUS'][0].", mas a base das catracas ainda não foi atualizada. Caso ela já tenha sido entregue, clique aqui.";
					} else {
						$lk_entrega = 0;
						$msg_aviso = "Uma carteirinha impressa no campus ".$ret_fila['CAMPUS'][0]." já foi entregue em ".$ret_fila['DAT_ENTREGA'][0].", mas a base das catracas ainda não foi atualizada.";
					}

/*
					// antigo - era usado qdo a entrega só podia ser feita após a atualização da base das catracas (cron) - tabela LOG
					$chk_gera = 0;
					$lk_cps = 0;
					$msg_aviso = "Já foi impressa uma carteirinha para este aluno no campus ".$ret_fila['CAMPUS'][0].", mas a base das catracas ainda não foi atualizada. Aguarde alguns minutos.";
*/
				}
			} else {
				// verifica se está na fila de impressão log (já foi impresso)
				$sql = "select *
					from catracas.impressao_alunos_log i
					where i.RA = ".$res_pesq['RA'][$i]."
							and i.curso = '" . $res_pesq['CURSO'][$i] . "'
					order by i.dat_imp desc";
				$num_fila_log = $bd_rafael->executaSelect($sql, $ret_fila_log);

				if ($num_fila_log > 0) {
					$qfl = "fila_log";
					$cod_fila = $ret_fila_log['COD'][0];

					// se ja foi impresso, verifica se já foi entregue
					// caso nao tenha sido entregue
					if ($ret_fila_log['DAT_ENTREGA'][0] == null) {
						$chk_gera = 0;
						$lk_cps = 0;
						$lk_entrega = 1;
						$msg_aviso = "Já foi impressa uma carteirinha para este aluno no campus ".$ret_fila_log['CAMPUS'][0]." em ".$ret_fila_log['DAT_IMP'][0].". Caso ela já tenha sido entregue, clique aqui.";
					} else {
						$chk_gera = 0;
						$lk_entrega = 0;
						if ($ret['TP_ACESSO'][0] == "LIDER")
							$lk_cps = 1;
						else
							$lk_cps = 0;
						$msg_aviso = "Uma carteirinha impressa no campus ".$ret_fila_log['CAMPUS'][0]." já foi entregue em ".$ret_fila_log['DAT_ENTREGA'][0].".";
					}
				} else {
					// verifica se já está no SQL Server
					$sql =  "select cred_numero, --crpes_datasaida,
							case
								when crpes_datasaida >= getdate() then 'Liberado'
								else 'Bloqueado'
							end SITUACAO
							from cred_pessoas
							where pes_numero = '1' + replicate(0,11-len(".$res_pesq['RA'][$i].")) + '".$res_pesq['RA'][$i]."'
							order by crpes_datasaida desc";

					$num_ss = $bd_ss->executaSelect($sql, $ret_ss);

					if ($num_ss > 0) {
						$qfl = "ss";

						$sql = "select ca.dat_acesso max_dat_acesso
										from catracas.acessos_aln ca
										where ca.mtr_num = ".$res_pesq['RA'][$i]."
										order by ca.dat_acesso desc";
						$num_acessos = $bd->executaSelect($sql, $ret_acessos);

						if ($num_acessos > 0) {
							$ultimo_acesso = "O último acesso deste aluno foi em ".$ret_acessos['MAX_DAT_ACESSO'][0].".";
						} else {
							$ultimo_acesso = "Este aluno não possui marcação de passagem nas catracas.";
						}

						if ($ret_ss['SITUACAO'][0] == 'Bloqueado') {
							$msg_aviso = "Este aluno já possui carteirinha e está bloqueada. ".$ultimo_acesso;
						} else {
							$msg_aviso = "Este aluno já possui carteirinha e está liberada. ".$ultimo_acesso;
						}
						if ($ret['TP_ACESSO'][0] == "LIDER")
							$lk_cps = 1;
						else
							$lk_cps = 0;
						$chk_gera = 0;
						$lk_entrega = 0;
					} else {
						if ($ret['TP_ACESSO'][0] == "LIDER" || $ret['TP_ACESSO'][0] == "BACK") {
							$lk_cps = 1;
						} else {
							$lk_cps = 0;
						}
						$chk_gera = 1;
						$lk_entrega = 0;
						$msg_aviso = "Este aluno não possui carteirinha e nem solicitação de impressão. Clique aqui se quiser enviar para a fila de impressão de outro campus.";
					}
				}
			}
?>
		<tr>
		<td align="center">
<?
			if ($chk_gera == 1) {
?>
			<input type="checkbox" name="chk_fila[]"
<?
				if ($ret['TP_ACESSO'][0] == "ATENDENTE")
					echo " disabled ";
?>
			value="<? echo $res_pesq['RA'][$i] . '|' . $res_pesq['PIDM'][$i] . '|' . $res_pesq['PLT_COD'][$i] . '|' . $res_pesq['PROGRAM'][$i] ?>">
<?
			} else {
				echo "&nbsp;";
			}
?>
		</td>
		<td align="right" width="55"><?= $res_pesq[RA][$i] ?></td>
		<td align="left">
<?
			if ($lk_cps == 1) {
				if ($qfl == "ss") {
?>
			<a class="link_reimp" href="index.php?acao=reimp&fl=ss&cd=0&ra=<?=$res_pesq[RA][$i]?>&pidm=<?=$res_pesq[PIDM][$i]?>&term=<?=$res_pesq[PLT_COD][$i]?>&program=<?=$res_pesq[PROGRAM][$i]?>&f_RA=<?= $_REQUEST['f_RA'] ?>&f_nome=<?= $_REQUEST['f_nome'] ?>">
<?
				} elseif ($qfl == "fila_log") {
?>
			<a class="link_reimp" href="index.php?acao=reimp&fl=fila_log&cd=<?=$cod_fila?>&ra=<?=$res_pesq[RA][$i]?>&pidm=<?=$res_pesq[PIDM][$i]?>&term=<?=$res_pesq[PLT_COD][$i]?>&program=<?=$res_pesq[PROGRAM][$i]?>&f_RA=<?= $_REQUEST['f_RA'] ?>&f_nome=<?= $_REQUEST['f_nome'] ?>">
<?
				} else {
?>
			<a class="link" href="index.php?acao=alt_cps&fl=<?=$qfl?>&cd=<?=$cod_fila?>&ra=<?=$res_pesq[RA][$i]?>&pidm=<?=$res_pesq[PIDM][$i]?>&term=<?=$res_pesq[PLT_COD][$i]?>&program=<?=$res_pesq[PROGRAM][$i]?>&f_RA=<?= $_REQUEST['f_RA'] ?>&f_nome=<?= $_REQUEST['f_nome'] ?>">
<?
				}
			} elseif ($lk_entrega == 1) {
?>
			<a class="link" href="index.php?acao=entrega&fl=<?=$qfl?>&cd=<?=$cod_fila?>&ra=<?=$res_pesq[RA][$i]?>&pidm=<?=$res_pesq[PIDM][$i]?>&term=<?=$res_pesq[PLT_COD][$i]?>&program=<?=$res_pesq[PROGRAM][$i]?>&f_RA=<?= $_REQUEST['f_RA'] ?>&f_nome=<?= $_REQUEST['f_nome'] ?>">
<?
			}
?>
			<span onmouseover="return escape('<?= $msg_aviso ?>')">
				<?= $res_pesq[NOME][$i] ?>
				</span>
<?

			if ($lk_cps == 1 || $qfl == "ss") {
?>
			</a>
<?
			}
?>
		</td>
		<td align="left" width="95"><?= $res_pesq[TIP_CUR][$i] ?></td>
		<td align="left"><?= $res_pesq[CURSO][$i] ?></td>
		<td align="center"><?= $res_pesq[SER][$i] ?></td>
		<td align="center"><?= $res_pesq[CPS][$i] ?></td>
		<td align="center"><?= $res_pesq[PLT_COD][$i] ?></td>
		</tr>
<?
		}
?>
	</table>
	</td>
</tr>
<tr>
	<td align=middle>
	<br><input type="button" class="texto-preto-lt" value="Enviar para fila de impressão"
<?
				if ($ret['TP_ACESSO'][0] == "ATENDENTE")
					echo " disabled ";
?>
	onClick="javascript:return verificaForm(document.frm_escolhe);">
	</td>
</tr>
</form>

<?
	}
}
?>
<tr>
	<td align=middle>
	<br><br>
	</td>
</tr>
</table>

<script language="JavaScript" type="text/javascript" src="<?= pegaPath() ?>include/wz_tooltip.js"></script>

<script language="javaScript">
function selectCheckBox(obj, f)
{
var name = "";
if (obj.checked)
{
	for (i=0; i<f.length; i++)
	{
		if (f.elements[i].type == "checkbox")
			f.elements[i].checked = true
	}
}
else
{
	for (i=0; i<f.length; i++)
	{
		if (f.elements[i].type == "checkbox")
			f.elements[i].checked = false;
	}
}
}

function verificaForm(f) {
	var sel = 0;
	for (i=0; i<f.length; i++)
	{
		if (f.elements[i].type == "checkbox") {
			if (f.elements[i].checked == true && f.elements[i].name != "chk_sel_all")
				sel = 1
		}
	}
	if (sel == 0) {
		alert("Nenhum aluno foi selecionado.");
		return false;
	} else {
		f.submit();
	}
	return true;
}
</script>