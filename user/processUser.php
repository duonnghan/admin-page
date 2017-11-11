<?php
require_once '../../library/config.php';
require_once '../library/functions.php';

checkUser();

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

	case 'add' :
		addUser();
		break;

	case 'modify' :
		modifyUser();
		break;

	case 'delete' :
		deleteUser();
		break;


	default :
	    // nếu hành động không được định nghĩa hoặc không biết, chuyển tới trang thành viên chính
		header('Location: index.php');
}


function addUser()
{
    $userName = $_POST['txtUserName'];
	$password = $_POST['txtPassword'];



	// kiểm tra username tồn tại chưa
	$sql = "SELECT user_name
	        FROM tbl_user
			WHERE user_name = '$userName'";
	$result = dbQuery($sql);

	if (dbNumRows($result) == 1) {
		header('Location: index.php?view=add&error=' . urlencode('Username already taken. Choose another one'));
	} else {
		$sql   = "INSERT INTO tbl_user (user_name, user_password, user_regdate)
		          VALUES ('$userName', PASSWORD('$password'), NOW())";

		dbQuery($sql);
		header('Location: index.php');
	}
}

/*
	Chỉnh sửa thành viên
*/
function modifyUser()
{
	$userId   = (int)$_POST['hidUserId'];
	$password = $_POST['txtPassword'];

	$sql   = "UPDATE tbl_user
	          SET user_password = PASSWORD('$password')
			  WHERE user_id = $userId";

	dbQuery($sql);
	header('Location: index.php');

}

/*
	Xóa thành viên
*/
function deleteUser()
{
	if (isset($_GET['userId']) && (int)$_GET['userId'] > 0) {
		$userId = (int)$_GET['userId'];
	} else {
		header('Location: index.php');
	}


	$sql = "DELETE FROM tbl_user
	        WHERE user_id = $userId";
	dbQuery($sql);

	header('Location: index.php');
}
?>
