<?php 

if (!defined( 'ABSPATH')) exit;

/**
*  Encryption 
*/
class WPMS_encryption{
	
	public $dir_path;

	function __construct($dir_path){	

		$this->dir_path = $dir_path;
	
	}


	public function generate_salt(){

		$file = $this->dir_path.'/salt.php';


		if ( file_exists($file) == false ){
			

			$rand   = rand(1,1000); 
			$rand  .= date('Y-m-d h:i:sa');
			$data   = '<?php
				if (!defined( "ABSPATH")) exit;
				//escape from sql hacking 
				$WPMS_salt = "'.md5($rand).'";
			?>';
			$handle = fopen($file, 'w');
			fwrite($handle, $data);
			fclose($handle);

		}


	}


	public function delete_salt(){

		$file = $this->dir_path.'/salt.php';

		if ( file_exists($file) ){

			unlink($file);
		}
	}

 

	public function data_encrypt($text, $salt){

	 	$salt = md5($salt);

	    return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}

 
	public function data_decrypt($text, $salt){

	 	$salt = md5($salt);
	 	
	    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}





}