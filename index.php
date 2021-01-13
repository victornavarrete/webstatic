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

	$env = ($_ENV["ENV"])?$_ENV["ENV"]:null;
	switch ($env) {
		case 'development':
			define("ENVIRONMENT", 'development');
			define("LOCALE", 'es_CL');
			define("TIMEZONE", 'America/Santiago'); 
			define('BASE_URL', 'webstatic.com'); 
			define('WEB_TITLE', 'webstatic (development mode)'); 
			break;  
		default:
			define("ENVIRONMENT", 'production');
			define("LOCALE", 'es_CL');
			define("TIMEZONE", 'America/Santiago'); 
			define('BASE_URL', 'webstatic.com'); 
			define('WEB_TITLE', 'webstatic'); 
			break;
	}

	$configs = [
				'name' => WEB_TITLE, 
				'desc' => 'webstatic is a basic webpage with a index.php',
				'ga_key' => 'UA-XXXXX-Y', 
				'page_default' => 'home',
				]; 


	define('CONFIG', $configs );  

	define('PAGE_PATH', __DIR__.'/pages'); 
	define('PARTIALS_PATH', __DIR__.'/partials'); 

	// COMPOSER 
	require __DIR__ . '/vendor/autoload.php';
   
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

	function load_partial($path, $var_array =array()){ 
		$path = rtrim($path, '/'); 
		$_local_filepath = PARTIALS_PATH.DIRECTORY_SEPARATOR.$path; 
		extract($var_array);  
		if(file_exists($_local_filepath.'.php')){ 
			include($_local_filepath.'.php'); 
		}elseif(file_exists($_local_filepath.DIRECTORY_SEPARATOR.'index.php')){ 
			include($_local_filepath.DIRECTORY_SEPARATOR.'index.php'); 
		}  
	}

	function set_error($code = 404, $text ='404 Not Found'){
		header($_SERVER["SERVER_PROTOCOL"].' '.$text, true, $code);  
		if(file_exists(PAGE_PATH.DIRECTORY_SEPARATOR.$code.'.html')){ 
			include(PAGE_PATH.DIRECTORY_SEPARATOR.$code.'.html'); 
		}else{
			echo '404 Not Found.';
		} 

	}

	function webstatic($uri_base, $show_not_found=true){
		$uri = rtrim($uri_base, '/');  
		$filepath = PAGE_PATH.DIRECTORY_SEPARATOR.$uri;  
		if(file_exists($filepath.'.php')){ 
			include($filepath.'.php'); 
		}elseif(file_exists($filepath.'.html')){ 
			include($filepath.'.html'); 
		}elseif(file_exists($filepath.DIRECTORY_SEPARATOR.'index.php')){ 
			include($filepath.DIRECTORY_SEPARATOR.'index.php'); 
		}elseif(file_exists($filepath.DIRECTORY_SEPARATOR.'index.html')){ 
			include($filepath.DIRECTORY_SEPARATOR.'index.html'); 
		}else{

			// Dinamic router by Altorouter
			$r = new AltoRouter();  
			$r->map( 'GET', '/post/[i:id][*:trailing]', function( $id , $trailing ) {   
				// example from array (from db is better with Medoo)
				$posts = [
					['title'=>'test 0','text'=>'lorem ipsum'],
					['title'=>'test 1','text'=>'lorem ipsum'],
					['title'=>'test 2','text'=>'lorem ipsum']
				]; 

				$post = $posts[$id];
				if($post){
					load_partial('post', ['post'=>$post]);
				}else{
					set_error(404,'404 Not Found');   
					die();
				}  
			});
 
			$match = $r->match($uri,'GET'); 
			unset($r);
			if( is_array($match) && is_callable( $match['target'] ) ) {
				call_user_func_array( $match['target'], $match['params'] ); 
			} else {
				 if($show_not_found){  
					set_error(404,'404 Not Found');
					die();
				}  
			} 
 
		}  
	}

	// prepare uri
	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
	$uri = in_array($uri, ['','/'])?CONFIG['page_default']:$uri; 
	webstatic($uri, true); 