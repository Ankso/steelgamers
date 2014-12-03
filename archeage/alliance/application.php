<?php 
require($_SERVER['DOCUMENT_ROOT'] . "/../common/SharedDefines.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/PreparedStatements.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../common/Common.php");
require($_SERVER['DOCUMENT_ROOT'] . "/../classes/Database.Class.php");

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	$allowedOnlineMembersValues = array("5-10", "10-20", "20-30", "30-40", "40-50", "50+");
	$allowedTotalMembersValues = array("10-20", "20-40", "40-70", "70-100", "100-150", "150+");
	if (isset($_POST['name']) && isset($_POST['contacts']) && isset($_POST['webpage'])
		&& isset($_POST['onlineMembers']) && isset($_POST['totalMembers']) && isset($_POST['description'])
		&& isset($_POST['joinReason']))
	{
		if (in_array($_POST['onlineMembers'], $allowedOnlineMembersValues) && in_array($_POST['totalMembers'], $allowedTotalMembersValues))
		{
			$db = new Database($DATABASES['USERS']);
			if ($db->ExecuteStmt(Statements::INSERT_ALLIANCE_APPLICATION,
				$db->BuildStmtArray("ssssssss", $_POST['name'], $_POST['contacts'], $_POST['webpage'],
				$_POST['onlineMembers'], $_POST['totalMembers'], $_POST['description'],
				$_POST['joinReason'], $_SERVER['REMOTE_ADDR'])))
			{
				echo "SUCCESS";
			}
			else
				echo "FAILURE";
		}
		// If we are here it means that the security checks made by javascript in the client were bypassed,
		// this is suspicious, it's better to don't send any info to the client.
	}
}
else
	header("location:http://archeage.steelgamers.es/alliance");
?>