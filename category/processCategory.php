<?php
require_once '../library/config.php';
require_once '../library/functions.php';

checkUser();

$action = isset($_GET['action']) ? $_GET['action'] : '';
switch ($action) {

    case 'add' :
        addCategory();
        break;

    case 'modify' :
        modifyCategory();
        break;

    case 'delete' :
        deleteCategory();
        break;

    case 'deleteImage' :
        deleteImage();
        break;


    default :
        // nếu hành động không định nghĩa hoặc không biết
        // gửi tới trang danh mục chính
        header('Location: index.php');
}


/*
    thêm 1 danh mục
*/
function addCategory()
{
    $name        = $_POST['txtName'];
    $description = $_POST['mtxDescription'];
    $image       = $_FILES['fleImage'];
    $parentId    = $_POST['hidParentId'];

    $catImage = uploadImage('fleImage', SRV_ROOT . 'images/category/');

    $sql   = "INSERT INTO tbl_category (cat_parent_id, cat_name, cat_description, cat_image)
              VALUES ($parentId, '$name', '$description', '$catImage')";
    $result = dbQuery($sql) or die('Cannot add category' . mysql_error());

    header('Location: index.php?catId=' . $parentId);
}

/*
    Upload 1 bức ảnh và trả về tên bức ảnh
*/
function uploadImage($inputName, $uploadDir)
{
    $image     = $_FILES[$inputName];
    $imagePath = '';

    // nếu 1 file được đưa ra
    if (trim($image['tmp_name']) != '') {
        // lấy ảnh mở rộng
        $ext = substr(strrchr($image['name'], "."), 1);

        // tạo tên file ảnh ngẫu nhiên
        $imagePath = md5(rand() * time()) . ".$ext";

		// kiểm tra chiều dài ảnh. Nếu quá to
		// chiều dài phải được resize lại
		$size = getimagesize($image['tmp_name']);

		if ($size[0] > MAX_CATEGORY_IMAGE_WIDTH) {
			$imagePath = createThumbnail($image['tmp_name'], $uploadDir . $imagePath, MAX_CATEGORY_IMAGE_WIDTH);
		} else {
			// đưa ảnh vào danh mục ảnh
			// nếu không có set $imagePath thành rỗng
			if (!move_uploaded_file($image['tmp_name'], $uploadDir . $imagePath)) {
				$imagePath = '';
			}
		}
    }


    return $imagePath;
}

/*
    Hiệu chỉnh 1 danh mục
*/
function modifyCategory()
{
    $catId       = (int)$_GET['catId'];
    $name        = $_POST['txtName'];
    $description = $_POST['mtxDescription'];
    $image       = $_FILES['fleImage'];

    $catImage = uploadImage('fleImage', SRV_ROOT . 'images/category/');

    // nếu upload ảnh mới
    // xóa ảnh cũ
    if ($catImage != '') {
        _deleteImage($catId);
		$catImage = "'$catImage'";
    } else {
		// để danh mục ảnh như vốn nó đã thế
		$catImage = 'cat_image';
	}

    $sql    = "UPDATE tbl_category
               SET cat_name = '$name', cat_description = '$description', cat_image = $catImage
               WHERE cat_id = $catId";

    $result = dbQuery($sql) or die('Cannot update category. ' . mysql_error());
    header('Location: index.php');
}

/*
    Gỡ bỏ danh mục
*/
function deleteCategory()
{
    if (isset($_GET['catId']) && (int)$_GET['catId'] > 0) {
        $catId = (int)$_GET['catId'];
    } else {
        header('Location: index.php');
    }

	// tìm tất cả danh mục con
	$children = getChildren($catId);

	// tạo 1 mảng chứa danh mục và tất cả danh mục con
	$categories  = array_merge($children, array($catId));
	$numCategory = count($categories);

	// gỡ tất cả ảnh sản phẩm & thumbnail
	// nếu danh mục sản phẩm là  $categories
	$sql = "SELECT pd_id, pd_image, pd_thumbnail
	        FROM tbl_product
			WHERE cat_id IN (" . implode(',', $categories) . ")";
	$result = dbQuery($sql);

	while ($row = dbFetchAssoc($result)) {
		@unlink(SRV_ROOT . PRODUCT_IMAGE_DIR . $row['pd_image']);
		@unlink(SRV_ROOT . PRODUCT_IMAGE_DIR . $row['pd_thumbnail']);
	}

	// xóa sản phẩm
	$sql = "DELETE FROM tbl_product
			WHERE cat_id IN (" . implode(',', $categories) . ")";
	dbQuery($sql);

	// sau đó xóa ảnh danh mục
	_deleteImage($categories);

    // cuối cùng xóa danh mục trong database;
    $sql = "DELETE FROM tbl_category
            WHERE cat_id IN (" . implode(',', $categories) . ")";
    dbQuery($sql);

    header('Location: index.php');
}


/*
	tìm danh mục con của $catId
*/
function getChildren($catId)
{
    $sql = "SELECT cat_id ".
           "FROM tbl_category ".
           "WHERE cat_parent_id = $catId ";
    $result = dbQuery($sql);

	$cat = array();
	if (dbNumRows($result) > 0) {
		while ($row = dbFetchRow($result)) {
			$cat[] = $row[0];

			// gọi lại hàm để tìm các con
			$cat  = array_merge($cat, getChildren($row[0]));
		}
    }

    return $cat;
}


/*
    Xóa ảnh danh mục
*/
function deleteImage()
{
    if (isset($_GET['catId']) && (int)$_GET['catId'] > 0) {
        $catId = (int)$_GET['catId'];
    } else {
        header('Location: index.php');
    }

	_deleteImage($catId);

	// Cập nhật tên ảnh trong database
	$sql = "UPDATE tbl_category
			SET cat_image = ''
			WHERE cat_id = $catId";
	dbQuery($sql);

    header("Location: index.php?view=modify&catId=$catId");
}

/*
	Xóa 1 danh mục ảnh where category = $catId
*/
function _deleteImage($catId)
{
    // ta sẽ trả về trạng thái
    // ảnh được xóa thành công
    $deleted = false;

	// lấy ảnh
    $sql = "SELECT cat_image
            FROM tbl_category
            WHERE cat_id ";

	if (is_array($catId)) {
		$sql .= " IN (" . implode(',', $catId) . ")";
	} else {
		$sql .= " = $catId";
	}

    $result = dbQuery($sql);

    if (dbNumRows($result)) {
        while ($row = dbFetchAssoc($result)) {
	        // xóa file ảnh
    	    $deleted = @unlink(SRV_ROOT . CATEGORY_IMAGE_DIR . $row['cat_image']);
		}
    }

    return $deleted;
}

?>
