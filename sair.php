<?php
session_destroy();

unset($_COOKIE['banner_pidm'], $_COOKIE['banner_session_id']);
setcookie('banner_pidm', null, -1, '/');
setcookie('banner_session_id', null, -1, '/');
header("Location: /rcfs/cartao_acesso/");

exit;