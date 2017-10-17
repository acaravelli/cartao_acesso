<STYLE TYPE="text/css">
<!--
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
	table { border-color: black; border-collapse: collapse; }
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
        BLOQUEAR/LIBERAR CARTEIRINHA<br>
    </td>
  </tr>
  
  <tr>
    <td align="left" class="texto-preto-lt">
        <a href="bloquear_liberar_full.php?acao=pesquisando&f_tipo=<?= $f_tipo ?>&f_RA=<?= $f_RA ?>&f_nome=<?= $f_nome ?>">[Voltar]</a>
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
			<td align="left">RA/crachá:</td>
			<td align="left"><?= $res[RA][0] ?></td>
		  </tr> 
		  <tr>
			<td align="left" width="70">Nome:</td>
			<td align="left"><?= $res[NOME][0] ?></td>		
		  </tr> 
		  <tr>
			<td align="left">Tipo:</td>
			<td align="left"><?= $res[TIPO][0] ?></td>
		  </tr> 
		  <tr>
			<td align="left">Situação:</td>
			<td align="left"><?= $res[SITUACAO][0] ?></td>
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
    <td align="center">
	  <form name="frm" action="<?=pegaURL()?>" method="post">
	  <input type="hidden" name="acao" value="gravando_sit">
	  <input type="hidden" name="cred_numero" value="">
	  <input type="hidden" name="bloq_lib" value="">
	  
	  <input type="hidden" name="pes_numero" value="<?= $pes_numero ?>">
	  <input type="hidden" name="f_tipo" value="<?= $f_tipo ?>">
	  <input type="hidden" name="f_RA" value="<?= $f_RA ?>">
	  <input type="hidden" name="f_nome" value="<?= $f_nome ?>">
	
		<table cellpadding="2" cellspacing="0" border="1" class=texto-preto-lt>
		  <tr height="16">
			<td class="texto-preto3" align="center" width="90">Nº CARTÃO</td>
			<td class="texto-preto3" align="center" width="90">SITUAÇÃO</td>
			<td class="texto-preto3" align="center">&nbsp;</td>			
		  </tr>			
<?
$liberado = 0;
for ($i=0; $i < $num_cartoes; $i++) {
	if ($res_cartoes[SITUACAO][$i] == "Liberado")
		$liberado = 1;
?>	
		  <tr>
			<td align="center"><?= $res_cartoes[CRED_NUMERO][$i] ?></td>
			<td align="center"><?= $res_cartoes[SITUACAO][$i] ?></td>
			<td align="center">
<?
	if ($res_cartoes[SITUACAO][$i] == "Liberado") {
?>			
			  <input type="button" class="texto-preto-lt" value="Bloquear" onClick="javascript:return envia('bloq', <?= $res_cartoes[CRED_NUMERO][$i] ?>);">
<?
	} else {
		if ($liberado == 0) {
?>			
			  <input type="button" class="texto-preto-lt" value="Liberar" onClick="javascript:return envia('lib', <?= $res_cartoes[CRED_NUMERO][$i] ?>);">
<?
		}
	}
?>
			</td>			
		  </tr>			
<?
}
?>
		</table>
	  </form>		
	</td>
  </tr>
  
  <tr>
	<td align=middle>
	  &nbsp;
	</td>
  </tr>
</table>

<script language="javaScript">
function envia(bloqlib, cred_num) {
	document.frm.bloq_lib.value = bloqlib;
	document.frm.cred_numero.value = cred_num;
	document.frm.submit();			
	return true;		
}
</script>
