<?php
class File
{
	public $src, $properties, $upload_dest;
	private $brp;
	public function __construct($src, $brp = "")
	{
		$this->brp = $brp;
		$href = explode("!", $src);
		if (file_exists($this->brp.$href[0])) 
		{
			$this->src = $href[0];
			$this->properties = pathinfo($href[0]);
			$this->properties['extension'] = strtolower($this->properties['extension']);
			if (isset($href[1]) && is_dir($this->brp.$href[1]) && is_writable($this->brp.$href[1])) 
			{
				$this->upload_dest = $href[1].md5($this->properties['filename']).".".$this->properties['extension'];
			}
			else{
				throw new Exception("Upload directory not set or not found!", 404);
			}
		}
		else{
			throw new Exception("File (".$src.") not found!", 404);
		}
	}

}
?>