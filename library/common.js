/*
cắt đoạn trắng từ đầu tới cuối của 1 string
Input : a string
*/
function trim(str)
{
	return str.replace(/^\s+|\s+$/g,'');
}

/*
textBox chỉ chứa số
*/
function checkNumber(textBox)
{
	while (textBox.value.length > 0 && isNaN(textBox.value)) {
		textBox.value = textBox.value.substring(0, textBox.value.length - 1)
	}

	textBox.value = trim(textBox.value);

}

/*
	Check nếu thành phần form rỗng
	Nếu hiện ra thông báo lỗi và tập trung vào thành phần đó
*/
function isEmpty(formElement, message) {
	formElement.value = trim(formElement.value);

	_isEmpty = false;
	if (formElement.value == '') {
		_isEmpty = true;
		alert(message);
		formElement.focus();
	}

	return _isEmpty;
}

/*
	Set giá trị trong combo box là giá trị được chọn
*/
function setSelect(listElement, listValue)
{
	for (i=0; i < listElement.options.length; i++) {
		if (listElement.options[i].value == listValue)	{
			listElement.selectedIndex = i;
		}
	}
}
