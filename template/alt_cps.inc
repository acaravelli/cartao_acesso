<style>
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
</style>

<table width="700" cellpadding="0" cellspacing="0" border="0" align="left">
  <tr>
    <td>
      &nbsp;
    </td>
  </tr>

  <tr>
    <td align="center" class="texto-preto">
        CONSULTA DE ALUNOS<br>
    </td>
  </tr>

  <tr>
    <td align="left" class="texto-preto-lt">
        <a href="index.php?acao=pesquisando&f_RA=<?= $f_RA ?>&f_nome=<?= $f_nome ?>">[Voltar]</a>
    </td>
  </tr>

  <tr>
    <td>
      &nbsp;
    </td>
  </tr>

  <tr>
    <td align="center">
	  <table cellpadding="0" cellspacing="2" border="0" class="texto-preto-lt">
		  <tr>
			<td align="left">ID:</td>
			<td align="left"><?= $res[RA][0] ?></td>
		  </tr>
		  <tr>
			<td align="left" width="70">Aluno:</td>
			<td align="left"><?= $res[NOME][0] ?></td>
		  </tr>
		  <tr>
			<td align="left">Segmento:</td>
			<td align="left"><?= $res[TIP_CUR][0] ?></td>
		  </tr>
		  <tr>
			<td align="left">Curso:</td>
			<td align="left"><?= $res[CURSO][0] ?></td>
		  </tr>
		  <tr>
			<td align="left">Série:</td>
			<td align="left"><?= $res[SER][0] ?></td>
		  </tr>
		  <tr>
			<td align="left">Campus:</td>
			<td align="left"><?= $res[CPS][0] ?></td>
		  </tr>
		  <tr>
			<td align="left">Term:</td>
			<td align="left"><?= $res[PLT_COD][0] ?></td>
		  </tr>
	  </table>
	</td>
  </tr>

   <tr>
    <td>
      &nbsp;
    </td>
  </tr>

  <tr>
	<td class=texto-preto-lt>
<?
$act = "";
$qfila = "";

// caso esteja na fila de impressao
if ($num_fila > 0) {
	$qfila = "fila_imp";
	// e ainda não tenha sido impressa
	if ($ret_fila['IMPRESSO'][0] == 0) {
		$act = "cps";
		echo "A solicitação encontra-se na fila do <b>campus ".$ret_fila[CAMPUS][0]."</b>.
		<br>Para alterar o campus onde a carteirinha será impressa, escolha abaixo:";
	// e ja tenha sido impressa (cron nao rodou ainda)
	} else {
		// caso ainda não tenha sido entregue
		if ($ret_fila['DAT_ENTREGA'][0] == null) {
			$act = "entr";
			echo "Já foi impressa uma carteirinha para este aluno no <b>campus ".$ret_fila[CAMPUS][0]."</b>, mas a base das catracas ainda não foi atualizada.
			<br>Clique no botão abaixo para marcar essa carteirinha como entregue ao aluno:";
		} else {
			echo "Uma carteirinha impressa no <b>campus ".$ret_fila[CAMPUS][0]."</b> já foi entregue ao aluno em <b>".$ret_fila['DAT_ENTREGA'][0]."</b>,
			<br>mas a base das catracas ainda não foi atualizada. Aguarde alguns minutos.";
		}
	}

} elseif ($num_fila_log > 0) {
	$qfila = "fila_log";
	if ($ret_fila_log['DAT_ENTREGA'][0] == null) {
		$act = "entr";
		echo "Já foi impressa uma carteirinha para este aluno no <b>campus ".$ret_fila_log[CAMPUS][0]."</b> em <b>".$ret_fila_log['DAT_IMP'][0]."</b>.
		<br>Clique no botão abaixo para marcar essa carteirinha como entregue ao aluno:";
	} else {
		$act = "reimp_entregue";
		echo "Uma carteirinha impressa no <b>campus ".$ret_fila_log['CAMPUS'][0]."</b> já foi entregue ao aluno em <b>".$ret_fila_log['DAT_ENTREGA'][0]."</b>.";
	}
} elseif ($num_ss > 0) {
	$act = "reimp";
	$qfila = "fila_ss";
	if ($ret_ss['SITUACAO'][0] == 'Bloqueado') {
		echo "<b>Este aluno já possui carteirinha e está bloqueada.";
		echo $ultimo_acesso."</b>";
		if ($ret['TP_ACESSO'][0] == "LIDER") {
			echo "<br><br><br>Para enviar para a fila de impressão e imprimir uma nova carteirinha (sem custo),<br>escolha o campus onde a carteirinha será impressa:";
		}
	} else {
		echo "<b>Este aluno já possui carteirinha e está liberada. ";
		echo $ultimo_acesso."</b>";
		if ($ret['TP_ACESSO'][0] == "LIDER") {
			echo "<br><br><br>Para enviar para a fila de impressão e imprimir uma nova carteirinha (sem custo),<br>escolha o campus onde a carteirinha será impressa:";
		}
	}
} else {
	$act = "cps";
	$novo = "S";
    echo "Este aluno ainda não possui carteirinha e nem solicitação de impressão.";
	if ($ret['TP_ACESSO'][0] == "LIDER" || $ret['TP_ACESSO'][0] == "BACK") {
		echo "<br>Para enviar para a fila de impressão, escolha o campus onde a carteirinha será impressa:";
	}
}
?>
	</td>
  </tr>

<?
if ($act == "entr" || $act == "reimp_entregue")	{
	if ($act == "entr")	{
?>
  <form name="frm" action="<?=pegaURL()?>" method="post">
  <input type="hidden" name="acao" value="gravando_entr">
  <input type="hidden" name="fila" value="<?= $qfila ?>">

  <input type="hidden" name="cd" value="<?= $cd ?>">

  <input type="hidden" name="ra" value="<?= $ra ?>">
  <input type="hidden" name="pidm" value="<?= $pidm ?>">
  <input type="hidden" name="term" value="<?= $term ?>">
  <input type="hidden" name="program" value="<?= $program ?>">

  <input type="hidden" name="f_RA" value="<?= $f_RA ?>">
  <input type="hidden" name="f_nome" value="<?= $f_nome ?>">

  <tr>
	<td align=middle>
	  <br><input type="submit" value="Entregue">
	</td>
  </tr>
  </form>

<?
	}
	if ($ret['TP_ACESSO'][0] == "LIDER" && $qfila != "fila_imp") {
?>
  <tr>
	<td class=texto-preto-lt><br><br>OU<br>
		<br><br>Caso a impressão tenha dado algum problema, envie para a fila novamente <br>(reimpressão sem custo para o aluno).<br>Escolha o campus onde a carteirinha será impressa:
	</td>
  </tr>
  <form name="frm_cr" action="<?=pegaURL()?>" method="post">
  <input type="hidden" name="acao" value="gravando_reimp">
  <input type="hidden" name="fila" value="<?= $qfila ?>">

  <input type="hidden" name="cd" value="<?= $cd ?>">

  <input type="hidden" name="ra" value="<?= $ra ?>">
  <input type="hidden" name="pidm" value="<?= $pidm ?>">
  <input type="hidden" name="term" value="<?= $term ?>">
  <input type="hidden" name="program" value="<?= $program ?>">

  <input type="hidden" name="f_RA" value="<?= $f_RA ?>">
  <input type="hidden" name="f_nome" value="<?= $f_nome ?>">

  <tr>
    <td align="center">
	  <table width="70%" cellpadding="12" cellspacing="0" border="0">
	    <tr>
		  <td>
		    <table width="100%" cellpadding="0" cellspacing="0" border="0" class=texto-preto-lt>
			  <tr>
			    <td align="center">
				  <input type="radio" name="f_cps" value="VO" <? if ($cps == 'VO') echo 'checked'; ?>>VO &nbsp;&nbsp;&nbsp;&nbsp;
  				  <input type="radio" name="f_cps" value="CE" <? if ($cps == 'CE') echo 'checked'; ?>>CE &nbsp;&nbsp;&nbsp;&nbsp;
		  		  <input type="radio" name="f_cps" value="MO" <? if ($cps == 'MO') echo 'checked'; ?>>MO &nbsp;&nbsp;&nbsp;&nbsp;
				  <input type="radio" name="f_cps" value="VA" <? if ($cps == 'VA') echo 'checked'; ?>>VA &nbsp;&nbsp;&nbsp;&nbsp;
				  <input type="radio" name="f_cps" value="PA" <? if ($cps == 'PA') echo 'checked'; ?>>PA &nbsp;&nbsp;&nbsp;&nbsp;
				  <input type="radio" name="f_cps" value="PA2" <? if ($cps == 'PA2') echo 'checked'; ?>>PA2
			    </td>
			  </tr>
		    </table>
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
  <tr>
    <td align="center">
	  <table cellpadding="3" cellspacing="0" border="0">
		  <tr>
			<td align="center" class="texto-preto-lt">Indique qual o motivo da reimpressão:</td>
		  </tr>
		  <tr>
			<td align="center" class=texto-preto-lt>
<?
		if ($num_acessos > 0 || $ret_fila_log['DAT_ENTREGA'][0] != null) {
			$acessou = "disabled";
			$acessou_fonte = "#ACACAC";
		}
?>
			  <input type="radio" name="f_mtv" value="DEF" checked>Defeito &nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="radio" name="f_mtv" value="NRC" <?= $acessou ?>><font color=<?= $acessou_fonte ?>>Não recebimento</font>
			</td>
		  </tr>
		  <tr>
			<td align="left" class="texto-preto-lt">Observação:</td>
		  </tr>
		  <tr>
			<td align="center" class="texto-preto-lt">
			  <input type="text" name="txt_obs" size=80 maxlength=200 class="texto-preto-lt"><br><br>
			</td>
		  </tr>
	  </table>
	</td>
  </tr>

<?
		// ALTERAR NOME QUE SERÁ IMPRESSO
/*
		//se estiver na fila de log ou se estiver na fila de impressão
		if ($num_fila_log > 0 or $num_fila > 0) {
			if ($num_fila > 0)
				$nome_imp = $ret_fila[NOME][0];
			else
				$nome_imp = $ret_fila_log[NOME][0];
		//se não estiver na fila de log e nem na fila de impressão
		} else {
			$nome_imp = $res[NOME][0];
		}
*/
		//se estiver na fila de impressão
		if ($num_fila > 0)
			$nome_imp = $ret_fila[NOME][0];
		//se não estiver na fila de impressão
		else
			$nome_imp = $res[NOME][0];

?>
   <tr>
    <td class=texto-preto-lt>Nome que será impresso:
      <?= substr($nome_imp,0,30) ?>
    </td>
  </tr>
<?
		if ($ret['TP_ACESSO'][0] == "LIDER") {
			if (strlen($res[NOME][0]) <= 30)
				$habil = "disabled";
?>
   <tr>
    <td class=texto-preto-lt>
      <input type="text" name="nome_impresso" readonly="readonly" size=38 maxlength=30 class="texto-preto-lt" value="<?= substr($nome_imp,0,30) ?>" <?= $habil ?>>
	  <font class="texto-preto-sm"> (máx. 30 caracteres)</font>
    </td>
  </tr>
<?
		}
?>

  <tr>
	<td align=middle>
<!--	  <input type="button" class="texto-preto-lt" value="Ok" onClick="javascript:return verificaForm(document.frm_cr.txt_obs, document.frm_cr);">-->
		<br>
	  <input type="submit" class="texto-preto-lt" value="Ok">
	</td>
  </tr>
  </form>

<?
	}
?>

<?
} elseif ($act == "cps" || $act == "reimp") {
	if ($ret['TP_ACESSO'][0] == "ATENDENTE" && $novo == "S") {

	} else {
		if ($ret['TP_ACESSO'][0] == "LIDER" || ($ret['TP_ACESSO'][0] == "BACK" && $act == "cps") ) {
?>
  <form name="frm_cr" action="<?=pegaURL()?>" method="post">
<?
			if ($act == "cps") {
?>
  <input type="hidden" name="acao" value="gravando_cps">
<?
			} else {
?>
  <input type="hidden" name="acao" value="gravando_reimp">
<?
			}
?>

  <input type="hidden" name="fila" value="<?= $qfila ?>">

  <input type="hidden" name="cd" value="<?= $cd ?>">

  <input type="hidden" name="ra" value="<?= $ra ?>">
  <input type="hidden" name="pidm" value="<?= $pidm ?>">
  <input type="hidden" name="term" value="<?= $term ?>">
  <input type="hidden" name="program" value="<?= $program ?>">

  <input type="hidden" name="f_RA" value="<?= $f_RA ?>">
  <input type="hidden" name="f_nome" value="<?= $f_nome ?>">

  <tr>
    <td align="center">
	  <table width="70%" cellpadding="12" cellspacing="0" border="0">
	    <tr>
		  <td>
		    <table width="100%" cellpadding="0" cellspacing="0" border="0" class=texto-preto-lt>
			  <tr>
			    <td align="center">
				  <input type="radio" name="f_cps" value="VO" <? if ($cps == 'VO') echo 'checked'; ?>>VO &nbsp;&nbsp;&nbsp;&nbsp;
  				  <input type="radio" name="f_cps" value="CE" <? if ($cps == 'CE') echo 'checked'; ?>>CE &nbsp;&nbsp;&nbsp;&nbsp;
		  		  <input type="radio" name="f_cps" value="MO" <? if ($cps == 'MO') echo 'checked'; ?>>MO &nbsp;&nbsp;&nbsp;&nbsp;
				  <input type="radio" name="f_cps" value="VA" <? if ($cps == 'VA') echo 'checked'; ?>>VA &nbsp;&nbsp;&nbsp;&nbsp;
				  <input type="radio" name="f_cps" value="PA" <? if ($cps == 'PA') echo 'checked'; ?>>PA &nbsp;&nbsp;&nbsp;&nbsp;
				  <input type="radio" name="f_cps" value="PA2" <? if ($cps == 'PA2') echo 'checked'; ?>>PA2
			    </td>
			  </tr>
		    </table>
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
<?
			if ($act == "reimp") {
?>
  <tr>
    <td align="center">
	  <table cellpadding="3" cellspacing="0" border="0">
		  <tr>
			<td align="center" class="texto-preto-lt">Indique qual o motivo da reimpressão:</td>
		  </tr>
		  <tr>
			<td align="center" class=texto-preto-lt>
<?
if ($num_acessos > 0) {
	$acessou = "disabled";
	$acessou_fonte = "#ACACAC";
}
?>
			  <input type="radio" name="f_mtv" value="DEF" checked>Defeito &nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="radio" name="f_mtv" value="NRC" <?= $acessou ?>><font color=<?= $acessou_fonte ?>>Não recebimento</font>
			</td>
		  </tr>
		  <tr>
			<td align="left" class="texto-preto-lt">Observação:</td>
		  </tr>
		  <tr>
			<td align="center" class="texto-preto-lt">
			  <input type="text" name="txt_obs" size=80 maxlength=200 class="texto-preto-lt"><br><br>
			</td>
		  </tr>
	  </table>
	</td>
  </tr>
<?
			}
?>

<?
		// ALTERAR NOME QUE SERÁ IMPRESSO
/*
		//se estiver na fila de log ou se estiver na fila de impressão
		if ($num_fila_log > 0 or $num_fila > 0) {
			if ($num_fila > 0)
				$nome_imp = $ret_fila[NOME][0];
			else
				$nome_imp = $ret_fila_log[NOME][0];
		//se não estiver na fila de log e nem na fila de impressão
		} else {
			$nome_imp = $res[NOME][0];
		}
*/
		//se estiver na fila de impressão
		if ($num_fila > 0)
			$nome_imp = $ret_fila[NOME][0];
		//se não estiver na fila de impressão
		else
			$nome_imp = $res[NOME][0];

?>
   <tr>
    <td class=texto-preto-lt>Nome que será impresso:
      <?= substr($nome_imp,0,30) ?>
    </td>
  </tr>
<?
		if ($ret['TP_ACESSO'][0] == "LIDER") {
			if (strlen($res[NOME][0]) <= 30)
				$habil = "disabled";
?>
   <tr>
    <td class=texto-preto-lt>
      <input type="text" name="nome_impresso" readonly="readonly" size=38 maxlength=30 class="texto-preto-lt" value="<?= substr($nome_imp,0,30) ?>" <?= $habil ?>>
	  <font class="texto-preto-sm"> (máx. 30 caracteres)</font>
    </td>
  </tr>
<?
		}
?>

  <tr>
	<td align=middle>
<!--	  <input type="button" class="texto-preto-lt" value="Ok" onClick="javascript:return verificaForm(document.frm_cr.txt_obs, document.frm_cr);">-->
	<br>
	  <input type="submit" class="texto-preto-lt" value="Ok">
	</td>
  </tr>
  </form>

<?
		}
	}
}
?>

  <tr>
	<td align=middle>
	  &nbsp;
	</td>
  </tr>
</table>


<script language="javaScript">
function verificaForm(obj, f) {
	if (obj.value == "")
	{
		alert("O motivo da reimpressão é um campo obrigatório.");
		return false;
	}
	else
	{
		f.submit();
//		alert(obj.value);
	}
	return true;
}
</script>