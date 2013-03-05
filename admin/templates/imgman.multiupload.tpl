<script type="text/javascript" src="../javascript/jquery.growinguploader.js"></script>
<script type="text/javascript" src="../javascript/jquery/plugins/ajax.file.upload.js"></script>

<div id="noFlashProgressWindow" style="display: none; ">
</div>
<div id="uploadFormNoFlash">
%%LNG_noFlashImageUploadIntro%%<br /><br />
<form method="post" action="" enctype="multipart/form-data">
	<div class="Uploader"><input type="file" name="Filedata"  class="Button noflashUploadField" id="growingUpload_first" /> <input type="button" class="Button" value="Clear" /></div>
</form>
</div>

<script type="text/javascript">

$('#uploadButton').bind('click', AdminImageManager.UploadNonFlashImages);
$('#noFlashProgressWindow').html($('#ProgressWindow').html());

</script>