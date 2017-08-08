<?php 
class UploadSystem
{
	public $upload_toDB_string = null;

	private $brp, $DB_conf = ['connection'=>null, 'table'=>null, 'column'=>null, 'type' => 'separate'], $upload_conf = ['create_thumb' => true, 'thumb_size' => 100];

	public function __construct($config = null, $connection = null){
		$config_data = $this->get_data($config);
		$this->transferArrays($config_data, $this->upload_conf, false);
		if(is_a($connection, 'mysqli')){
			$this->DB_conf['connection']=$connection;
		}
	}

	public function setConnection($c){
		if (is_a($c, 'mysqli')) {
			$this->DB_conf['connection'] = $c;
		}
		else{
			throw new Exception('Invalid type of connection!', 1);
		}
	}

	public function Upload($files = null, $to = null, $database_set = null, $connection_DB = null){
		$is_insert_DB = false;
		$multi_srcs = '';
		if(is_a($connection_DB, 'mysqli')){
			$this->DB_conf['connection']=$connection_DB;
		}
		if (!$database_set == null) {
			$data = $this->get_data($database_set);	
			$this->transferArrays($data, $this->DB_conf);
			$is_insert_DB = true;		
		}	

		if (is_string($files) && is_string($to) && !$to == null) {
		}
		else if (is_array($files) && !empty($files) && !$to == null && is_dir($to)) {
		    $file_desc = $this->reArrayFiles($files);
		    foreach($file_desc as $k=>$file)
		    {
		    	if ($file['type'] == 'image/png' || $file['type'] == 'image/jpeg') {
		    		switch ($file['type']) {
			    		case 'image/png':
			    			$extension = '.png';
			    			break;
			    		case 'image/jpeg':
			    			$extension = '.jpg';
			    			break;
			    		default:
			    			throw new Exception("This type of image can't be uploaded!");
			    			break;
			    	}
		    		$newname = date('YmdHis',time()).mt_rand().$extension;
		    		$create_thumb = $this->upload_conf['create_thumb'];
		    		echo $create_thumb;
		    	}
		    	else{
		    		$newname = $file['name'];
		    		$create_thumb = false;
		    	}
		    	$new_href = $to.$newname;
		        if(move_uploaded_file($file['tmp_name'], $new_href))
		        {
		        	if ($create_thumb) {
		        		if(!$this->create_thumb($new_href))
		        		{
		        			throw new Exception("Error with creating thumb!", 1);
		        		}
			        }
			        if ($is_insert_DB && $this->DB_conf['type'] == "separate") {
		        		$this->UploadToDB($new_href);
	        		}
        			$multi_srcs = $new_href."!".$multi_srcs;
        			echo $multi_srcs."<br>";

		        }
		        else{
		        	throw new Exception("Failed to upload file!");
		        }
		    }
		    $this->upload_toDB_string = $multi_srcs;
		    if($is_insert_DB && $this->DB_conf['type'] == 'multiply'){
		    	$this->UploadToDB();
		    }
		}
		else{
			throw new Exception("Not found directory of faile! ");
		}
	}

	private function UploadToDB($href)
	{
		echo $href;
	}

	private function create_thumb($src, $to = null){
		$desired_width= $this->upload_conf['thumb_size'];
		if (!file_exists($src)) {
			throw new Exception("Image to thumb not found!", 404);
		}
		$src_info = pathinfo($src);
		$new_name = "thumb_".$src_info['basename'];
		if (!$to==null && is_dir($to)) {
			$dest = $to.$new_name;
		}
		else{
			$dest = dirname($src).DIRECTORY_SEPARATOR.$new_name;	
		}

		if ($src_info['extension'] == 'jpg') {
			$source_image = imagecreatefromjpeg($src);
		}
		else if($src_info['extension'] == 'png'){
			$source_image = imageCreateFromPng($src);
		}
		$width = imagesx($source_image);
		$height = imagesy($source_image);
		$desired_height = floor($height * ($desired_width / $width));
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);

		imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		if(imagejpeg($virtual_image, $dest)){
			return true;
		};
	}

	private function get_data($info){
		if(is_string($info))
		{
			$data = [];
			$data_str = explode(",", $info);
			for ($i=0; $i < count($data_str); $i++) { 
				$current_value = explode(":", $data_str[$i]);
				$data[trim($current_value[0])] = $current_value[1];	
			}
			return $data;;	
		}
		else if(is_array($info))
		{
			return $info;
		}
		else{
			throw new Exception("Invalid type of data!");
		}
		
	}

	private function reArrayFiles($file)
	{
	    $file_ary = array();
	    $file_count = count($file['name']);
	    $file_key = array_keys($file);
	    
	    for($i=0;$i<$file_count;$i++)
	    {
	        foreach($file_key as $val)
	        {
	            $file_ary[$i][$val] = $file[$val][$i];
	        }
	    }
	    return $file_ary;
	}

	private function transferArrays($arrA, $arrB, $throw = true){
		foreach ($arrB as $key => $property) {
			if(isset($arrA[$key])){
				$arrB[$key] = $arrA[$key];	
			}
			else if($arrB[$key] == null && $throw){
				throw new Exception("Missing argument ".$key."!");
			}
		}
	}

}

?>