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
<link rel="stylesheet" href="/js/jquery-ui-1.11.4.custom/jquery-ui.min.css" type="text/css" media="screen">
<table width="700" cellpadding="0" cellspacing="0" border="0" align="left">
<tr>
	<td>
	&nbsp;
	</td>
</tr>

<tr>
	<td align="center" class="texto-preto">
		RELATÓRIO DE CARTÕES IMPRESSOS
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
			<table cellpadding="12" cellspacing="0" border="1" width="75%">
			<tr>
				<td>
				<table cellpadding="2" cellspacing="0" border="0" class="texto-preto-lt">
					<tr>
					<td align="left">Data Inicial</td>
					<td align="left">
						<input class="texto-preto-lt" type="text" data-mask="dd/mm/yyyy" name="f_data" id="f_data" maxlength="10" size="10"  value="<?= $f_data ?>" />
					</td>
					<td align="left">Data Final</td>
					<td align="left">
						<input class="texto-preto-lt" type="text" data-mask="dd/mm/yyyy" name="f_data_fim" id="f_data_fim" maxlength="10" size="10" />
					</td>
					<td align="middle">
						<input type="submit" class="texto-preto-lt" value="Gerar Relatório CSV" name="b_REL" id="b_REL" />
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
			A página será atualizada em <strong id="contador"></strong> segundos.
		</td>
		</tr>

		<tr>
		<td align="middle"><BR>
			&nbsp;
		</td>
		</tr>


	</table>
	</form>
	</td>
</tr>
<?
	if ($num_pesq == 0) {
?>
<tr>
	<td class="texto-preto-lt">A pesquisa não retornou resultados.</td>
</tr>
<?
	} else {
?>
<tr>
	<td class="texto-preto-lt">Relatório do dia <strong><?php echo $f_data;?></strong></td>
</tr>
<tr>
	<td align="center">
	<table cellpadding="2" cellspacing="0" border="1" class="texto-preto-sm" width="100%">
		<thead>
			<tr>
				<th width="55">RA</th>
				<th>Aluno</td>
				<th width="95">Cartão</th>
				<th>Impresso</td>
				<th>Dt Impressão</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($ret['RA'] as $k => $ra) {$css = ($k % 2) ? " style=\"background-color:#ccc\"" : null?>
			<tr<?php echo $css;?>>
				<td width="55"><?php echo $ra?></td>
				<td><?php echo $ret['NOME'][$k]?></td>
				<td width="95"><?php echo $ret['ID'][$k]?></td>
				<td width="95"><?php echo $ret['IMPRESSO'][$k]?></td>
				<td><?php echo $ret['DAT_IMP_BR'][$k]?></td>
			</tr>
			<?php }?>
		</tbody>
	</table>
	</td>
</tr>
<?
	}
?>
</table>
<br><br><br><br><br><br><br><br><br><br><br>
<script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/jquery.maskedinput-1.4.1.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.10.4.custom/development-bundle/ui/i18n/jquery.ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="/rcfs/cartao_acesso/js/default.js"></script>