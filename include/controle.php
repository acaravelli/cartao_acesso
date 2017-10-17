<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/web/libs/php/classes/');
require 'Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance();

/**
 * Dump de uma variável.
 *
 * @param mixed $var Variável a ser visualizada.
 * @param string $label
*/
function d($var, $label = null)
{
	Zend_Debug::dump($var, $label);
}

/**
 * Dump de uma variável e paralisa o processamento da página.
 *
 * @param mixed $var Variável a ser visualizada.
 * @param string $label
 */
function dd($var, $label = null)
{
	d($var, $label);
	die;
}

/**
 * Compara duas datas.
 *
 * @param  DateTime $date1
 * @param  DateTime $date2
 * @return integer -1 => Data1 é anterior a Data2; 0 => Data1 é igual a Data2; 1 => Data1 é posterior a Data2
 */
function date_diff1(DateTime $date1, DateTime $date2)
{
	if ($date1 == $date2) {
		return 0;
	} elseif ($date1 > $date2) {
		return 1;
	} else {
		return -1;
	}
}

/**
 * Retorna perfil de acesso do usuário.
 *
 * @param integer $pidm
 * @return string
 */
function f_acesso($pidm)
{
	global $bd;

	$sql = "select (case
		when baninst1.f_uam_acesso_obj_pessoa('CARTAO_ACESSO_LIDER', '1.0', :pidm) = 1 then 'LIDER'
		when baninst1.f_uam_acesso_obj_pessoa('CARTAO_ACESSO_BACK', '1.0', :pidm) = 1 then 'BACK'
		when baninst1.f_uam_acesso_obj_pessoa('CARTAO_ACESSO_ATEND', '1.0', :pidm) = 1 then 'ATENDENTE'
		else null
		end) tp_acesso
	from dual";

	$num = $bd->executaSelectComBind($sql, $ret, array('pidm' => $pidm));

	return $ret;
}

$template = defineTemplate("PORTAL");
$base = 'web';