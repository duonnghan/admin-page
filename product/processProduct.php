<?php
require_once '../../library/config.php';
require_once '../library/functions.php';

checkUser();

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

	case 'addProduct' :
		addProduct();
		break;

	case 'modifyProduct' :
		modifyProduct();
		break;

	case 'deleteProduct' :
		deleteProduct();
		break;

	case 'deleteImage' :
		deleteImage();
		break;


	default :
	    // nếu hành động chưa định nghĩa hoặc không biết
		// chuyển tới trang sản phẩm chính
		header('Location: index.php');
}


function addProduct()
{
    $catId       = $_POST['cboCategory'];
    $name        = $_POST['txtName'];
	$description = $_POST['mtxDescription'];
	$price       = str_replace(',', '', (double)$_POST['txtPrice']);
	$qty         = (int)$_POST['txtQty'];

	$images = uploadProductImage('fleImage', SRV_ROOT . 'images/product/');

	$mainImage = $images['image'];
	$thumbnail = $images['thumbnail'];

	$sql   = "INSERT INTO tbl_product (cat_id, pd_name, pd_description, pd_price, pd_qty, pd_image, pd_thumbnail, pd_date)
	          VALUES ('$catId', '$name', '$description', $price, $qty, '$mainImage', '$thumbnail', NOW())";

	$result = dbQuery($sql);

	header("Location: index.php?catId=$catId");
}

/*
	Upload ảnh và trả về tên file ảnh đã upload
*/
function uploadProductImage($inputName, $uploadDir)
{
	$image     = $_FILES[$inputName];
	$imagePath = '';
	$thumbnailPath = '';

	// nếu file đã thêm
	if (trim($image['tmp_name']) != '') {
		$ext = substr(strrchr($image['name'], "."), 1);

		// tạo file mới ngẫu nhiên để tránh xung đột
		$imagePath = md5(rand() * time()) . ".$ext";

		list($width, $height, $type, $attr) = getimagesize($image['tmp_name']);

		// chắc chắn là chiều rộng ảnh không vượt quá chiều rộng tối đa cho phép
		if (LIMIT_PRODUCT_WIDTH && $width > MAX_PRODUCT_IMAGE_WIDTH) {
			$result    = createThumbnail($image['tmp_name'], $uploadDir . $imagePath, MAX_PRODUCT_IMAGE_WIDTH);
			$imagePath = $result;
		} else {
			$result = move_uploaded_file($image['tmp_name'], $uploadDir . $imagePath);
		}

		if ($result) {
			// tạo thumbnail
			$thumbnailPath =  md5(rand() * time()) . ".$ext";
			$result = createThumbnail($uploadDir . $imagePath, $uploadDir . $thumbnailPath, THUMBNAIL_WIDTH);

			// tạo thumbnail lỗi, xóa bức ảnh này đi
			if (!$result) {
				unlink($uploadDir . $imagePath);
				$imagePath = $thumbnailPath = '';
			} else {
				$thumbnailPath = $result;
			}
		} else {
			// sản phẩm không được upload / resiz
			$imagePath = $thumbnailPath = '';
		}

	}


	return array('image' => $imagePath, 'thumbnail' => $thumbnailPath);
}

/*
	Sửa sản phẩm
*/
function modifyProduct()
{
	$productId   = (int)$_GET['productId'];
    $catId       = $_POST['cboCategory'];
    $name        = $_POST['txtName'];
	$description = $_POST['mtxDescription'];
	$price       = str_replace(',', '', $_POST['txtPrice']);
	$qty         = $_POST['txtQty'];

	$images = uploadProductImage('fleImage', SRV_ROOT . 'images/product/');

	$mainImage = $images['image'];
	$thumbnail = $images['thumbnail'];

	// nếu đang upload ảnh mới
	// xóa ảnh cũ
	if ($mainImage != '') {
		_deleteImage($productId);

		$mainImage = "'$mainImage'";
		$thumbnail = "'$thumbnail'";
	} else {
		// nếu không cập nhật ảnh
		// chắc chắn đướng dẫn cũ trùng với trong database
		$mainImage = 'pd_image';
		$thumbnail = 'pd_thumbnail';
	}

	$sql   = "UPDATE tbl_product
	          SET cat_id = $catId, pd_name = '$name', pd_description = '$description', pd_price = $price,
			      pd_qty = $qty, pd_image = $mainImage, pd_thumbnail = $thumbnail
			  WHERE pd_id = $productId";

	$result = dbQuery($sql);

	header('Location: index.php');
}

/*
	Xóa sản phẩm
*/
function deleteProduct()
{
	if (isset($_GET['productId']) && (int)$_GET['productId'] > 0) {
		$productId = (int)$_GET['productId'];
	} else {
		header('Location: index.php');
	}

	// xóa mọi tham chiều tới sản phẩm từ tbl_order_item và tbl_cart
	$sql = "DELETE FROM tbl_order_item
	        WHERE pd_id = $productId";
	dbQuery($sql);

	$sql = "DELETE FROM tbl_cart
	        WHERE pd_id = $productId";
	dbQuery($sql);

	// lấy tên ảnh và thumbnail
	$sql = "SELECT pd_image, pd_thumbnail
	        FROM tbl_product
			WHERE pd_id = $productId";

	$result = dbQuery($sql);
	$row    = dbFetchAssoc($result);

	// xóa ảnh sản phẩm và thumbnail
	if ($row['pd_image']) {
		unlink(SRV_ROOT . 'images/product/' . $row['pd_image']);
		unlink(SRV_ROOT . 'images/product/' . $row['pd_thumbnail']);
	}

	// xóa sản phẩm trong database;
	$sql = "DELETE FROM tbl_product
	        WHERE pd_id = $productId";
	dbQuery($sql);

	header('Location: index.php?catId=' . $_GET['catId']);
}


/*
	Xóa ảnh sản phẩm
*/
function deleteImage()
{
	if (isset($_GET['productId']) && (int)$_GET['productId'] > 0) {
		$productId = (int)$_GET['productId'];
	} else {
		header('Location: index.php');
	}

	$deleted = _deleteImage($productId);

	// cập nhật ảnh và tên thumbnail trong database
	$sql = "UPDATE tbl_product
			SET pd_image = '', pd_thumbnail = ''
			WHERE pd_id = $productId";
	dbQuery($sql);

	header("Location: index.php?view=modify&productId=$productId");
}

function _deleteImage($productId)
{
	// ta sẽ trả về trạng thái dù cho ảnh đã xóa thành công
	$deleted = false;

	$sql = "SELECT pd_image, pd_thumbnail
	        FROM tbl_product
			WHERE pd_id = $productId";
	$result = dbQuery($sql) or die('Cannot delete product image. ' . mysql_error());

	if (dbNumRows($result)) {
		$row = dbFetchAssoc($result);
		extract($row);

		if ($pd_image && $pd_thumbnail) {
			// xóa file ảnh
			$deleted = @unlink(SRV_ROOT . "images/product/$pd_image");
			$deleted = @unlink(SRV_ROOT . "images/product/$pd_thumbnail");
		}
	}

	return $deleted;
}




?>
