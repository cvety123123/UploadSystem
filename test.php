<form action="#" method="post" multipart="" enctype="multipart/form-data">
    <input type="file" name="img[]" multiple>
    <input type="file" name="imgs_second[]" multiple>
    <input type="submit" name="upload_btn">
</form>

<?php
if (isset($_POST['upload_btn'])) {
	echo '<pre>';
	$img = $_FILES['img'];
	include "UploadSystem.php";
	include "../../crd-version2/config.php";
	$test = new UploadSystem(['create_thumb'=>false]);
	$test->Upload($img, "uploads/");
	echo $test->upload_toDB_string. "<br>";
	$test->Upload($_FILES['imgs_second'], "./");
	echo $test->upload_toDB_string;
	// function reArrayFiles($file)
	// {
	//     $file_ary = array();
	//     $file_count = count($file['name']);
	//     $file_key = array_keys($file);
	    
	//     for($i=0;$i<$file_count;$i++)
	//     {
	//         foreach($file_key as $val)
	//         {
	//             $file_ary[$i][$val] = $file[$val][$i];
	//         }
	//     }
	//     return $file_ary;
	// }

	// if(!empty($img))
	// {
	//     $img_desc = reArrayFiles($img);
	    
	//     foreach($img_desc as $val)
	//     {
	//         $newname = date('YmdHis',time()).mt_rand().'.jpg';
	//         move_uploaded_file($val['tmp_name'],'./'.$newname);
	//     }
	// }

	
}

?>
