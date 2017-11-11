<?php
if (!defined('WEB_ROOT')) {
	exit;
}

// lấy config hiện tại
$sql = "SELECT sc_name, sc_address, sc_phone, sc_email, sc_shipping_cost, sc_currency, sc_order_email
        FROM tbl_shop_config";
$result = dbQuery($sql);

// trích tùy chỉnh từ database
// chắc chắn truy vấn trả về dòng
if (dbNumRows($result) > 0) {
	extract(dbFetchAssoc($result));
} else {
	// từ khi truy vấn không trả về dòng nào ( có thể do chưa chạy file SQL )
	// để giá trị các biến rỗng hết
	$sc_name = $sc_address = $sc_phone = $sc_email = $sc_shipping_cost = $sc_currency = '';
	$sc_order_email = 'y';
}

// nhận đơn vị tiền tệ hiện có
$sql = "SELECT cy_id, cy_code
        FROM tbl_currency
		ORDER BY cy_code";
$result = dbQuery($sql);

$currency = '';
while ($row = dbFetchAssoc($result)) {
	extract($row);
	$currency .= "<option value=\"$cy_id\"";
	if ($cy_id == $sc_currency) {
		$currency .= " selected";
	}

	$currency .= ">$cy_code</option>\r\n";
}
?>
<p>&nbsp;</p>
<form action="processConfig.php?action=modify" method="post" name="frmConfig" id="frmConfig">
 <table width="100%" border="0" cellspacing="1" cellpadding="2" class="entryTable">
  <tr id="entryTableHeader">
   <td colspan="2">Tùy chỉnh cửa hàng</td>
  </tr>
  <tr>
   <td width="150" class="label">Tên cửa hàng</td>
   <td class="content"><input name="txtShopName" type="text" class="box" id="txtShopName" value="<?php echo $sc_name; ?>" size="50" maxlength="50"></td>
  </tr>
  <tr>
   <td width="150" class="label">Địa chỉ</td>
   <td class="content"><textarea name="mtxAddress" cols="50" rows="3" id="mtxAddress" class="box"><?php echo $sc_address; ?></textarea></td>
  </tr>
  <tr>
   <td width="150" class="label">Số điện thoại</td>
   <td class="content"><input name="txtPhone" type="text" class="box" id="txtPhone" value="<?php echo $sc_phone; ?>" size="30" maxlength="30"></td>
  </tr>
  <tr>
   <td class="label">Email</td>
   <td class="content"><input name="txtEmail" type="text" class="box" id="txtEmail" value="<?php echo $sc_email; ?>" size="30" maxlength="30"></td>
  </tr>
 </table>
 <p>&nbsp;</p>
 <table width="100%" border="0" cellspacing="1" cellpadding="2" class="entryTable">
  <tr id="entryTableHeader">
   <td colspan="2">Tùy chỉnh giá</td>
  </tr>
  <tr>
   <td width="150" class="label">Ngoại tệ</td>
   <td class="content"><select name="cboCurrency" id="cboCurrency" class="box">
<?php echo $currency; ?>
    </select>   </td>
  </tr>
  <tr>
   <td width="150" class="label">Giao dịch</td>
   <td class="content"><input name="txtShippingCost" type="text" class="box" id="txtShippingCost" value="<?php echo $sc_shipping_cost; ?>" size="5"></td>
  </tr>
  <tr>
    <td class="label">Gửi email khi có báo cáo mới </td>
    <td class="content"><input name="optSendEmail" type="radio" value="y" id="optEmail" <?php echo $sc_order_email == 'y' ? 'checked' : ''; ?> />
      <label for="optsEmail">Yes </label>
      <input name="optSendEmail" type="radio" value="n" id="optNoEmail" <?php echo $sc_order_email == 'n' ? 'checked' : ''; ?> />
      <label for="optNoEmail">No</label></td>
  </tr>
 </table>
 <p align="center">
  <input name="btnUpdate" type="submit" id="btnUpdate" value="Cập nhật" class="box">
 </p>
</form>
