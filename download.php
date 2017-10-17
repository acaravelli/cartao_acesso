<?php	
if($_GET['arquivo']){		
	$caminho 	= $_GET['arquivo'];	
	$file		= basename($caminho);
	$filetype	= filetype($caminho);
	$filesize	= filesize($caminho);	
	
	header("Content-Type: $filetype");
	header("Content-Length: $filesize");
	header("Content-Disposition: attachment; filename=\"$file\"; size=$filesize");
	readfile($caminho);
	exit;
}