<?php
// logout.php
session_start();
require_once 'classes/Session.php';

$session = new Session();
$session->logout();

header('Location: login.php?message=Vous avez été déconnecté avec succès');
exit;
?>