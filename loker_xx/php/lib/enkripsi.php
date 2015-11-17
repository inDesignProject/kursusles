<?php
class Enkripsi {
	var $skey 	= "SuPerEncKey2010"; 
 
    public  function safe_b64encode($string) {
 
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
 
	public function safe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
 
    public  function encode($value){ 
   
		if(!$value){return false;}
        $string = $value;
		$result = '';
		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->skey, ($i % strlen($this->skey))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}
        return trim($this->safe_b64encode($result)); 
    }
 
    public function decode($value){
 
		if(!$value){return false;}
		$string = $value;
		$result = '';
	  	$string = $this->safe_b64decode($string); 
	
		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->skey, ($i % strlen($this->skey))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
		}
	
	  return trim($result);
    }
}
?>