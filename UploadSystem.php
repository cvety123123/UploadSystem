<?php 
include "File.php";
class UploadSystem
{
	public $connection;
	private $brp, $DB_conf = ['connection'=>null, 'table'=>null, 'column'=>null, 'type' => 'separate'];

	public function __construct($c = null){
		if(is_a($c, 'mysqli')){
			$this->DB_conf['connection']=$c;
		}
	}

	public function Upload($files = null, $to = null, $database_set = null, $connection_DB = null){
		if (is_string($files) && is_string($to) && !$to == null) {
		}
		else if (is_array($files) && !empty($files) && !$to == null && is_dir($to)) {
		    $file_desc = $this->reArrayFiles($files);
		    
		    print_r($file_desc);
		    foreach($file_desc as $k=>$file)
		    {
		    	$create_thumb = null;
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
		    		$create_thumb = true;
		    	}
		    	else{
		    		$newname = $file['name'];
		    		$create_thumb = false;
		    	}
		    	$new_href = $to.$newname;
		        if(move_uploaded_file($file['tmp_name'], $new_href))
		        {
		        	if ($create_thumb) {
		        		$this->create_thumb($new_href);	
			        }
			        if(is_a($connection_DB, 'mysqli')){
						$this->DB_conf['connection']=$connection_DB;
					}
	        		if (!$database_set == null) {
	        			if(is_string($database_set))
	        			{
	        				$data = $this->get_data($database_set);	
	        			}
	        			else if(is_array($database_set))
        				{
        					$data = $database_set;
        				}
        				else{
        					throw new Exception("Invalid argument (must be string or array)!");
        				}
						foreach ($this->DB_conf as $key => $property) {
							echo $property;
							if(isset($data[$key])){
								if($property == null)
								{
									$this->DB_conf[$key] = $data[$key];	
								}
								else{
									throw new Exception("Missing argument ".$key."!");
								} 
							}
							
						}
					}
		        }
		        else{
		        	throw new Exception("Failed to upload file!");
		        }
		    }
		}
		else{
			throw new Exception("Not found directory of faile! ");
		}
	}

	private function UploadToDB($DB_conf = null, $href)
	{
		if (!$this->DB_conf['connection']==null) {
			if(!$this->DB_conf['connection'] == null && !$this->DB_conf['table']==null && !$this->DB_conf['column']==null){
			}
			else if (is_string($DB_conf)){
				$data = $this->get_data($DB_conf);
				if (isset($data['table']) && isset($data['column'])) {
					$this->DB_conf['table'] = $data['table'];
					$this->DB_conf['column'] = $data['column'];
				}
				else{
					throw new Exception("The unexepted or missing parameter in DB_conf!", 1);
				}
			}
			else{
				throw new Exception("Database table and column not set!", 1);
			}

			if(!$this->DB_conf['connection']->query('INSERT INTO '.$this->DB_conf['table'].' ('.$this->DB_conf['column'].') values ("'.$href.'")'))
			{
				throw new Exception($this->DB_conf['connection']->error, 1);
			}
		}
	}

	public static function create_thumb($src,$desired_width=100, $to = null){
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
		imagejpeg($virtual_image, $dest);
	}

	public static function get_data($str){
		$data = [];
		$data_str = explode(",", $str);
		for ($i=0; $i < count($data_str); $i++) { 
			$current_value = explode(":", $data_str[$i]);
			$data[trim($current_value[0])] = $current_value[1];	
		}
		return $data;
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

}

?>