<?php 
include "File.php";
class UploadSystem
{
	public $connection;
	private $brp, $DB_conf = ['connection'=>null, 'table'=>null, 'column'=>null];

	public function __construct($c = null, $DB_conf = null){
		if(is_a($c, 'mysqli')){
			$this->DB_conf['connection']=$c;
			if (is_string($DB_conf)) {
				$data = $this->get_data($DB_conf);
				if (isset($data['table']) && isset($data['column'])) {
					$this->DB_conf['table'] = $data['table'];
					$this->DB_conf['column'] = $data['column'];
				}
				else{
					throw new Exception("The unexepted or missing parameter in DB_conf!", 1);
				}
			}
		}
	}

	public function Upload($files = null, $to = null){
		if (is_string($files) && is_string($to) && !$to == null) {
		}
		else if (is_array($files) && !empty($files)) {
		    $img_desc = $this->reArrayFiles($files);
		    
		    foreach($img_desc as $val)
		    {
		        $newname = date('YmdHis',time()).mt_rand().'.jpg';
		        move_uploaded_file($val['tmp_name'],'./'.$newname);
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

	public static function create_thumb($src, $desired_width=100, $to){
		if (!file_exists($this->brp.$src)) {
			throw new Exception("Image to thumb not found!", 404);
		}

		$new_name = "thumb_".filename($src);
		if (!$to==null && is_dir($this->brp.$to)) {
			$dest = $to.$new_name;
		}
		else{
			$dest = dirname($src).DIRECTORY_SEPARATOR.$new_name;	
		}

		$source_image = imagecreatefromjpeg($src);
		$width = imagesx($source_image);
		$height = imagesy($source_image);
		$desired_height = floor($height * ($desired_width / $width));
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);

		imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		imagejpeg($virtual_image, $this->brp.$dest);
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