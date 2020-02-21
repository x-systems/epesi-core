<?php 

namespace Epesi\Core;

use atk4\ui\jsExpression;
use Epesi\Core\System\Modules\Concerns\HasLinks;
use Epesi\Core\System\SystemCore;
use Epesi\Core\System\Modules\ModuleManager;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UI extends \atk4\ui\App
{
	use HasLinks;
	
	public $version = '2.0.0-alpha1';
	
	public $cdn = [
			'semantic-ui'      => 'https://cdn.jsdelivr.net/npm/fomantic-ui@2.7.2/dist',
	];
	
	public $always_run = false;
	
	protected $url_building_ext = '';
	
	public function __construct($defaults = [])
	{
		parent::__construct([
				'title' => config('epesi.app.title', 'EPESI'),
		        'cdn' => array_merge($this->cdn, (array) config('epesi.app.cdn')),
				//TODO: set the skin from admin / user selection
		        'skin' => config('epesi.app.skin', $this->skin),
		        'template_dir' => array_merge(ModuleManager::collect('templates', $this->skin), (array) $this->template_dir)
		]);
	}
	
	final public static function module()
	{
		return SystemCore::class;
	}
	
	public function response()
	{
		return response($this->render());
	}
	
// 	public function getViewJS($actions)
// 	{
// 		$ready = new jsFunction($actions);
		
// 		return "<script page-pjax>\n".
// 				(new jQuery($ready))->jsRender().
// 				'</script>';
// 	}
	
	public function render()
	{	
		$this->module()::requireCSS('epesi.css');
		
		$this->addCsrfToken();
		
		$this->addFavIcon();
		
// 		$this->enablePjax();
		
		ob_start();
		
		$this->run();
		
		return ob_get_clean();
	}
	
	public function renderException($exception)
	{
	    ob_start();
	    if ($exception instanceof TokenMismatchException) {
	        $this->jsRedirectHomepage(__('Session expired! Redirecting to your home page ...'));
	    }
	    elseif ($exception instanceof NotFoundHttpException) {
	        $this->jsRedirectHomepage(__('Requested page not found! Redirecting to your home page ...'));
	    }	    
	    else {
	        $this->caughtException($exception);
	    }
	    
	    return ob_get_clean();
	}
	
	public function jsRedirectHomepage($message)
	{
	    $homepageUrl = url(HomePage\Models\HomePage::pathOfUser());
	    
	    $redirectJs = $this->jsRedirectConfirm($homepageUrl, $message)->jsRender();
	    
	    if ($this->isJsonRequest()) {
	        $this->outputResponseJSON([
	                'success'   => true,
	                'message'   => $message,
	                'atkjs'   => $redirectJs
	        ]);
	    }
	    else {
	        $this->outputResponseHTML('<script>' . $redirectJs . '</script>');
	    }
	}
	
	public function jsRedirectConfirm($page, $message)
	{
	    $redirectJs = $this->jsRedirect($page)->jsRender();
	    
	    return new jsExpression("if (confirm([])) { $redirectJs }", [$message]);
	}
	
	/**
	 * Initialize JS and CSS includes.
	 */
	public function initIncludes()
	{
// 		$this->requireJS(asset('js/app.js'));
// 		$this->requireCSS(asset('css/app.css'));
				
		//TODO: include below in app.js and app.css
		
		$localJs = url('storage/system/js');
		$localCss = url('storage/system/css');
		
		// jQuery
		$urlJs = $this->cdn['jquery']?? $localJs;
		$this->requireJS($urlJs.'/jquery.min.js');
		
		// Semantic UI
		$urlJs = $this->cdn['semantic-ui']?? $localJs;
		$urlCss = $this->cdn['semantic-ui']?? $localCss;
		$this->requireJS($urlJs.'/semantic.min.js');
		$this->requireCSS($urlCss.'/semantic.min.css');
		
		// Serialize Object
		$urlJs = $this->cdn['serialize-object']?? $localJs;
		$this->requireJS($urlJs.'/jquery.serialize-object.min.js');
		
		// Agile UI
		$urlJs = $this->cdn['atk']?? $localJs;
		$urlCss = $this->cdn['atk']?? $localCss;
		$this->requireJS($urlJs.'/atkjs-ui.min.js');
		$this->requireCSS($urlCss.'/agileui.css');
		
		// Draggable
		$urlJs = $this->cdn['draggable']?? $localJs;
		$this->requireJS($urlJs.'/draggable.bundle.js');
		
		// jQuery niceScroll	
		$urlJs = $this->cdn['jquery-nicescroll']?? $localJs;
		$this->requireJS($urlJs.'/jquery.nicescroll.js');
		
		// clipboard.js
		$urlJs = $this->cdn['clipboardjs']?? $localJs;
		$this->requireJS($urlJs.'/clipboard.js');
	}
	
	public function addCsrfToken()
	{
		$this->html->template->appendHTML('meta', $this->getTag('meta', ['name' => 'csrf-token', 'content' => csrf_token()]));

		$this->addJS('$.ajaxSetup({
			headers: {
				\'X-CSRF-TOKEN\': $(\'meta[name="csrf-token"]\').attr(\'content\')
			}
		})');
	}
	
	public function addFavIcon()
	{
		$this->html->template->appendHTML('HEAD', $this->getTag('link', ['rel' => 'shortcut icon', 'href' => config('epesi.app.favicon', url('favicon.png'))]));
	}
	
	public function enablePjax()
	{
		// pjax library
// 		$this->requireJS('https://cdn.jsdelivr.net/npm/pjax/pjax.js');
		
// 		$this->html->template->appendHTML('HEAD', '<script>
// 			$(function(){
// 				var pjax = new Pjax({
// 						"elements": ".pjax", 
// 						"selectors": [".atk-layout", "head > script[page-pjax]", "head > title"]
// 				});
// 			});
			
// 		</script>');
		
		// pjax-api library
		$this->requireJS('https://cdn.jsdelivr.net/npm/pjax-api@latest');

		$this->html->template->appendHTML('HEAD', '<script>

			$(function(){		
				const { Pjax } = require("pjax-api");		
				new Pjax({
						"links": ".pjax", 
						"areas": [".atk-layout", "head > script[page-pjax]", "head > title"]
				});
			});
			
		</script>');

		// common
		$this->addJs('$(".pjax").click(function(e) {
				window.onbeforeunload(e);
				
				if (e.returnValue == "unsaved" && ! confirm("Unsaved data. Continue?")) {
 					e.stopImmediatePropagation();
					window.stop();
 				}
		});');		
	}
	
	public function addJs($js, $args = [])
	{
		$this->html->js(true, new jsExpression($js, $args));
	}

	/**
	 * Override default method to make sure js script is included only once
	 * 
	 * {@inheritDoc}
	 * @see \atk4\ui\App::requireJS()
	 */
	public function requireJS($url, $isAsync = false, $isDefer = false)
	{
		static $cache;
		
		$key = md5(serialize(func_get_args()));
		
		if (! isset($cache[$key])) {
			$cache[$key] = true;
			
			parent::requireJS($url, $isAsync, $isDefer);
		}
				
		return $this;
	}
		
	/**
	 * Set the breadcrumb location
	 * 
	 * @param array|string $location
	 * @return null
	 */
	public function setLocation($location)
	{
		return $this->layout->setLocation($location);
	}
	
	public function packageInfo()
	{
		$content = file_get_contents(__DIR__ . '/../composer.json');
		
		return json_decode($content, true);
	}
}