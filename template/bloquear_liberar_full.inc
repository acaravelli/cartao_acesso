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
		BLOQUEAR/LIBERAR CARTEIRINHA
	</td>
</tr>

<tr>
	<td align="left" class="texto-preto-lt">
		<a href="index.php">[Consultar alunos]</a>
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

	<table width="100%" cellpadding="0" cellspacing="0" border="0" class=texto-preto-lt>
		<tr>
		<td align="center"><br>
			<table cellpadding="12" cellspacing="0" border="1">
			<tr>
				<td>
				<table cellpadding="2" cellspacing="0" border="0" class=texto-preto-lt>
					<tr>
					<td align="center" colspan="2">
						<input type="radio" name="f_tipo" value="1" <?= $f_tipo_A ?>> Aluno &nbsp;&nbsp;&nbsp;
						<input type="radio" name="f_tipo" value="2" <?= $f_tipo_F ?>> Funcionário<br><br>
					</td>
					</tr>
					<tr>
					<td width="62" align="left">
						RA/crachá
					</td>
					<td align="left">
						<input class=texto-preto-lt type="text" name="f_RA" maxlength="8" size="8" value='<?= $_REQUEST['f_RA'] ?>'>
					</td>
					</tr>
					<tr>
					<td align="left">
						Nome
					</td>
					<td align="left">
						<input class=texto-preto-lt type="text" name="f_nome" maxlength="40" size="20"  value='<?= $_REQUEST['f_nome'] ?>'>
					</td>
					</tr>
				</table>
				</td>
			  </tr>
			<table>
		</td>
		</tr>
		<tr>
		<td align=middle><BR>
			<input type="submit" class="texto-preto-lt" value="Ok" name="b_OK">
		</td>
		</tr>
	</table>
	</form>
	</td>
</tr>

<tr>
	<td>
	  Esta consulta retorna apenas os alunos/funcionários que já possuem carteirinha.<br><br>
	</td>
</tr>

<?
if ($acao == "pesquisando") {
	if ($num_pesq == 0) {
?>
<tr>
	<td class=texto-preto-lt>A pesquisa não retornou resultados.</td>
</tr>
<?
	} else {
?>

<tr>
	<td align="center">
	<table cellpadding="4" cellspacing="0" border="1" class=texto-preto-sm>
		<tr>
		<td class="texto-preto3" width=70>RA</td>
		<td class="texto-preto3">NOME</td>
		<td class="texto-preto3" width=95>TIPO</td>
		<td class="texto-preto3" width=75>SITUAÇÃO</td>
		</tr>
<?
		for ($i=0; $i < $num_pesq; $i++) {
?>
		<tr>
		<td align="right"><?= $res_pesq[RA][$i] ?>&nbsp;</td>
		<td align="left">
			<a class="link" href="bloquear_liberar_full.php?pes_numero=<?=$res_pesq[PES_NUMERO][$i]?>&acao=alt_sit&f_tipo=<?= $f_tipo ?>&f_RA=<?= $f_RA ?>&f_nome=<?= $f_nome ?>">
				<?= $res_pesq[NOME][$i] ?>
			</a>
		</td>
		<td align="center"><?= $res_pesq[TIPO][$i] ?></td>
		<td align="center"><?= $res_pesq[SITUACAO][$i] ?></td>
		</tr>
<?
		}
?>
	</table>
	</td>
</tr>

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
