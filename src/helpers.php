<?php

if (! function_exists('ui')) {
	function ui() : Epesi\Core\UI
    {
    	return resolve(Epesi\Core\UI::class);
    }
}

if (! function_exists('eval_css')) {
	function eval_css($css)
    {
    	ui()->addStyle($css);
    }
}

if (! function_exists('eval_js')) {
	function eval_js($js, $args = [])
    {
    	ui()->addJs($js, $args);
    }
}

if (! function_exists('load_css')) {
	function load_css($url)
    {
    	return ui()->requireCss($url);
    }
}

if (! function_exists('load_js')) {
	function load_js($url)
    {
    	return ui()->requireJs($url);
    }
}
