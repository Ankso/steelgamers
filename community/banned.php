<?php
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");

if (!isset($_GET['reason']))
{
    header("Location:http://steelgamers.es");
    exit();
}

$userId = $_GET['reason'];
$db = new Database($DATABASES['USERS']);
if ($result = $db->ExecuteStmt(Statements::SELECT_USERS_BANNED, $db->BuildStmtArray("i", $userId)))
{
    if ($row = $result->fetch_assoc())
    {
        $banReason = $row['ban_reason'];
        $banEnd = $row['ban_end'];
    }
}
else
{
    header("Location:http://steelgamers.es");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>SteelGamers - Cuenta baneada</title>
	<link type="text/css" rel="stylesheet" href="css/main.css">
</head>
<body>
    <div style="text-align:center; margin:50px;">Esta cuenta ha sido baneada. Raz&oacute;n: <?php echo $banReason; ?></div>
    <div style="text-align:center;">El baneo expira el: <?php echo date("d-m-Y H:i:s", strtotime($banEnd)); ?></div>
</body>
</html>