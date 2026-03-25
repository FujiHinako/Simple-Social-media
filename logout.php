<?php
session_start();
session_destroy();
header('Location: homepage.php?loggedout=1');
exit;
?>

