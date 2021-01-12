<?php

/*

APACHE (.htaccess)

	Options -Indexes
	DirectoryIndex index.php 
	 
	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php/$1 [L]

NGINX

	server {
		listen 127.0.0.1:80;
		server_name webstatic.com www.webstatic.com;

		root home/webstatic.com/public_html;

		index index.php index.html;
		log_not_found off;
		charset utf-8;

		location ~ /\. { deny all; }
		location = /browserconfig.xml { }
		location = /site.webmanifest { }
		location = /favicon.ico { }
		location = /humans.txt { }
		location = /robots.txt { }
		
		location / {
	                try_files $uri $uri/ /index.php?$query_string;
	       }

		location ~ \.php$ {
			fastcgi_pass 127.0.0.1:9071;
			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
			include fastcgi_params;
		}

	}

*/
	 
	define("ENVIRONMENT", 'production' );
	define("LOCALE", 'es_CL' );
	define("TIMEZONE", 'America/Santiago' ); 
	define('BASE_URL', 'webstatic.com' ); 

	$configs = [
				'name' => 'webstatic',
				'desc' => 'webstatic is a basic webpage with a index.php',
				'ga_key' => 'UA-XXXXX-Y', 
				]; 
	define('CONFIG', $configs ); 



	define('VIEWS_PATH', __DIR__.'/views'); 
	define('PARTIALS_PATH', __DIR__.'/partials'); 
   
	function is_http_secure(){ 
		if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		{
			return TRUE;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
		{
			return TRUE;
		}
		elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
		{
			return TRUE;
		}

		return FALSE;
	}

	define('PROTOCOL', is_http_secure()?'https://':'http://');

	function base_url($path){    
		$base_url = BASE_URL;  

		if(is_array($path) && !empty($path)){ $path = implode('/', $path); }  

		if(empty($base_url )){ 
			if (isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME']) || isset($_SERVER['SERVER_ADDR'])) {    
				if($_SERVER['HTTP_HOST']){
					$base_url =  $_SERVER['HTTP_HOST'];
				}elseif($_SERVER['SERVER_NAME']){
					$base_url =  $_SERVER['SERVER_NAME'];
				}else{
					$base_url =  $_SERVER['SERVER_ADDR'];
				}  
				 
			} else {
				$base_url = 'localhost';
			}
		} 
		$p = sprintf( "%s://%s/%s", PROTOCOL, $base_url, $path );
		return $p; 
	}

	function redirect($url =null){
		header("Location: ".$url);
		die();
	} 

	function slugify($text) { // code from https://www.30secondsofcode.org/php/s/slugify
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);
		$text = trim($text, " \t\n\r\0\x0B-");
		if (empty($text)) {
		return 'n-a';
		}
		return $text;
	} 


	function date_human($string){ 
		return date('d/m/Y', strtotime($string)); 
	} 

	function load_partial($path){
		$path = rtrim($path, '/'); 
		$base = PARTIALS_PATH.DIRECTORY_SEPARATOR.$path; 

		if(file_exists($base.'.php')){ 
			include($base.'.php'); 
		}elseif(file_exists($base.'.html')){ 
			include($base.'.html'); 
		}elseif(file_exists($base.DIRECTORY_SEPARATOR.'index.php')){ 
			include($base.DIRECTORY_SEPARATOR.'index.php'); 
		}elseif(file_exists($base.DIRECTORY_SEPARATOR.'index.html')){ 
			include($base.DIRECTORY_SEPARATOR.'index.html'); 
		}  
	}

	function set_error($code = 404, $text ='404 Not Found'){
		header($_SERVER["SERVER_PROTOCOL"].' '.$text, true, $code);  
		if(file_exists(VIEWS_PATH.DIRECTORY_SEPARATOR.$code.'.html')){ 
			include(VIEWS_PATH.DIRECTORY_SEPARATOR.$code.'.html'); 
		}else{
			echo '404 Not Found.';
		} 
	}

	function load_page($path, $show_not_found=true){
		$path = rtrim($path, '/'); 
		$base = VIEWS_PATH.DIRECTORY_SEPARATOR.$path; 

		if(file_exists($base.'.php')){ 
			include($base.'.php'); 
		}elseif(file_exists($base.'.html')){ 
			include($base.'.html'); 
		}elseif(file_exists($base.DIRECTORY_SEPARATOR.'index.php')){ 
			include($base.DIRECTORY_SEPARATOR.'index.php'); 
		}elseif(file_exists($base.DIRECTORY_SEPARATOR.'index.html')){ 
			include($base.DIRECTORY_SEPARATOR.'index.html'); 
		}elseif($show_not_found){  
			set_error(404,'404 Not Found');
			exit(1);  
		}  
	} 

	// COMPOSER 
	// require __DIR__ . '/vendor/autoload.php';
	$path_only = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
	$path_only = in_array($path_only, ['','/'])?'home':$path_only;
	load_page($path_only, true); 