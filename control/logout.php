<?php
require_once __DIR__ . '/session.php';

logout_user();
header('Location: ../view/frontOffice/index.php');
exit;
