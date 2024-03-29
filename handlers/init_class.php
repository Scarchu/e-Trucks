<?php
class sc_trucks{

	var $server_path;
	var $e107_dirs;
	var $http_path;
	var $https_path;
	var $file_path;
	var $relative_base_path;
	var $_ip_cache;

	function sc_trucks($e107_paths, $e107_root_path){
		$this->e107_dirs = $e107_paths;
		$this->set_paths();
		$this->file_path = $this->fix_windows_paths($e107_root_path)."/";
	}
	function set_base_path()
	{
		global $pref;
		$this->base_path = $this->http_path;
	}
	
	function set_paths(){
		global $UPLOADS_DIRECTORY, $ADMIN_DIRECTORY, $THEMES_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $PLUGINS_DIRECTORY;
		$path = ""; $i = 0;
		while (!file_exists("{$path}class.php")) {
			$path .= "../";
			$i++;
		}
		if($_SERVER['PHP_SELF'] == "") { $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME']; }

		$http_path = dirname($_SERVER['PHP_SELF']);
		$http_path = explode("/", $http_path);
		$http_path = array_reverse($http_path);
		$j = 0;
		while ($j < $i) {
			unset($http_path[$j]);
			$j++;
		}
		$http_path = array_reverse($http_path);
		$this->server_path = implode("/", $http_path)."/";
		$this->server_path = $this->fix_windows_paths($this->server_path);

		if ($this->server_path == "//") {
			$this->server_path = "/";
		}
		$this->relative_base_path = $path;
		$this->http_path = "http://{$_SERVER['HTTP_HOST']}{$this->server_path}";
		$this->https_path = "https://{$_SERVER['HTTP_HOST']}{$this->server_path}";
		$this->file_path = $path;

		if(!defined("e_HTTP") || !defined("e_ADMIN") )
		{
			define("e_HTTP", $this->server_path);
			define("e_BASE", $this->relative_base_path);
			define("e_ADMIN", e_BASE.$ADMIN_DIRECTORY);
			define("e_UPLOADS", e_BASE.$UPLOADS_DIRECTORY);
			define("e_THEME", e_BASE.$THEMES_DIRECTORY);
			define("e_HANDLER", e_BASE.$HANDLERS_DIRECTORY);
			define("e_PLUGINS", e_BASE.$PLUGINS_DIRECTORY);
			define("e_LANGUAGEDIR", e_BASE.$LANGUAGES_DIRECTORY);

			define("e_ADMIN_ABS", e_HTTP.$ADMIN_DIRECTORY);
			define("e_UPLOADS_ABS", e_HTTP.$UPLOADS_DIRECTORY);
			define("e_THEME_ABS", e_HTTP.$THEMES_DIRECTORY);
			define("e_HANDLER_ABS", e_HTTP.$HANDLERS_DIRECTORY);
			define("e_PLUGINS_ABS", e_HTTP.$PLUGINS_DIRECTORY);
			define("e_LANGUAGEDIR_ABS", e_HTTP.$LANGUAGES_DIRECTORY);


			if(isset($_SERVER['DOCUMENT_ROOT'])) 
			{ 
			  define("e_DOCROOT", $_SERVER['DOCUMENT_ROOT']."/"); 
			} 
			else 
			{ 
			  define("e_DOCROOT", false); 
			}
		}
	}

	function fix_windows_paths($path) {
		$fixed_path = str_replace(array('\\\\', '\\'), array('/', '/'), $path);
		$fixed_path = (substr($fixed_path, 1, 2) == ":/" ? substr($fixed_path, 2) : $fixed_path);
		return $fixed_path;
	}
	
	/**
	 * Get the current user's IP address
	 *
	 * @return string
	 */
	function getip() 
	{
		if(!$this->_ip_cache)
		{
			$ip=$_SERVER['REMOTE_ADDR'];
			if (getenv('HTTP_X_FORWARDED_FOR')) 
			{
				if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3)) 
				{
					$ip2 = array('#^0\..*#', 
							'#^127\..*#', 							// Local loopbacks
							'#^192\.168\..*#', 						// RFC1918 - Private Network
							'#^172\.(?:1[6789]|2\d|3[01])\..*#', 	// RFC1918 - Private network
							'#^10\..*#', 							// RFC1918 - Private Network
							'#^169\.254\..*#', 						// RFC3330 - Link-local, auto-DHCP 
							'#^2(?:2[456789]|[345][0-9])\..*#'		// Single check for Class D and Class E
							);
					$ip = preg_replace($ip2, $ip3[1], $ip);
				}
			} 
			if ($ip == "") 
			{
				$ip = "x.x.x.x";
			}
			$this->_ip_cache = $ip;
		}
		return $this->_ip_cache;
	}
	
	function get_memory_usage(){
		if(function_exists("memory_get_usage")){
			$memusage = memory_get_usage();
			$memunit = CORE_LAN_B;
			if ($memusage > 1048576){
				$memusage = $memusage / 1024;
				$memunit = CORE_LAN_KB;
			}
			if ($memusage > 1048576){
				$memusage = $memusage / 1024;
				$memunit = CORE_LAN_MB;
			}
			if ($memusage > 1048576){
				$memusage = $memusage / 1024;
				$memunit = CORE_LAN_GB;
			}
			return (number_format($memusage, 0).$memunit);
		} else {
			return ('Unknown');
		}
	}
}
?>