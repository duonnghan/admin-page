<?php
ini_set('display_errors', 'On');
//ob_start("ob_gzhandler");
error_reporting(E_ALL);

// bắt đầu session
session_start();

// Cấu hình kết nối database
$dbHost = '127.0.0.1:8080';
$dbUser = 'root';
$dbPass = '';
$dbName = 'qlsach';

// tùy chỉnh web root và server root cho ứng dụng book shopping cart
//__FILE__: đường dẫn đến tập tin hiện tại
$thisFile = str_replace('\\', '/', __FILE__);
$docRoot = $_SERVER['DOCUMENT_ROOT'];

$webRoot  = str_replace(array($docRoot, 'library/config.php'), '', $thisFile);
$srvRoot  = str_replace('library/config.php', '', $thisFile);

define('WEB_ROOT', 'localhost:8080/'.$webRoot);
define('SRV_ROOT', $srvRoot);

// các thư mục mà bạn muốn chứa tất cả thư mục và ảnh sản phẩm
define('CATEGORY_IMAGE_DIR', 'images/category/');
define('PRODUCT_IMAGE_DIR',  'images/product/');

// vài giới hạn cỡ cho danh mục và ảnh sản phẩm

// tất cả ảnh danh mục với chiều rộng không quá 75 pixel
define('MAX_CATEGORY_IMAGE_WIDTH', 75);

// ta cần giới hạn chiều rộng ảnh sản phẩm
// chỉnh giá trị là 'true' thì tốt hơn
define('LIMIT_PRODUCT_WIDTH',     true);

// chiều rộng tối đa cho tất cả ảnh sản phẩm
define('MAX_PRODUCT_IMAGE_WIDTH', 300);

// chiều rộng cho thumbnail sản phẩm
define('THUMBNAIL_WIDTH',         75);

if (!get_magic_quotes_gpc()) {
	if (isset($_POST)) {
		foreach ($_POST as $key => $value) {
			$_POST[$key] =  trim(addslashes($value));
		}
	}

	if (isset($_GET)) {
		foreach ($_GET as $key => $value) {
			$_GET[$key] = trim(addslashes($value));
		}
	}
}

// Từ tất cả các trang sẽ yêu cầu quyền truy cập database
// và tất cả thư viện đều được sử dụng
// load các thư viện này tại đây
require_once 'database.php';
require_once 'common.php';

// lấy tùy chỉnh cửa hàng ( name, addres, ... ), cần tất cả các trang
$shopConfig = getShopConfig();
?>
