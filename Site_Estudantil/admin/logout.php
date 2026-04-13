<?php
// admin/logout.php — EduNews
session_start();
session_destroy();
header('Location: ../public/index.php');
exit;
