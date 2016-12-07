<?php
spl_autoload_register(function($class){
	
	$prefix = dirname(__FILE__);
	$top_dirs = array("lib","helper","core");
	$path = str_replace("\\","/",$class);

	foreach($top_dirs as $top_dir){
		$pwd = $prefix . "/" . $top_dir . "/" . $path . ".php";
		if(file_exists($pwd)){
			include_once($pwd);
		}
	}

});

?>
