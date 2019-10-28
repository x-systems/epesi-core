<?php

if (! function_exists('epesi')) {
	function epesi() : Epesi\Core\App
    {
    	return resolve(Epesi\Core\App::class);
    }
}

if (! function_exists('eval_css')) {
	function eval_css($css)
    {
    	epesi()->addStyle($css);
    }
}

if (! function_exists('eval_js')) {
	function eval_js($js, $args = [])
    {
    	epesi()->addJS($js, $args);
    }
}

if (! function_exists('load_css')) {
	function load_css($url)
    {
    	return epesi()->requireCSS($url);
    }
}

if (! function_exists('load_js')) {
	function load_js($url)
    {
    	return epesi()->requireJS($url);
    }
}
