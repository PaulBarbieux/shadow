jQuery(document).ready(function(){
	// Confirm
	$("._confirm").click(function(){
		if (confirm($(this).attr('confirm'))) {
			return true;
		} else {
			return false;
		}
	});
	// Image preview
	$("FORM .img-select").change(function(){
		$(this).parent().parent().find(".img-preview").attr('src',$("#IMAGES_FOLDER").val() + $(this).val());
	});
});