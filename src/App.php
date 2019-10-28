<?php 

namespace Epesi\Core;

use atk4\ui\App as BaseApp;
use Epesi\Core\UI\Seeds\AdminLayout;
use atk4\ui\jsExpression;
use Epesi\Core\Integration\Concerns\HasLinks;
use Epesi\Core\System\SystemCore;

class App extends BaseApp
{
	use HasLinks;
	
	public $version = '2.0.0-alpha1';
	
	public $always_run = false;
	protected $url_building_ext = '';
	
	public function __construct($defaults = [])
	{
		$this->cdn = array_merge($this->cdn, config('epesi.app.cdn', []));
		
		//TODO: set the skin from admin / user selection
		$skin = config('epesi.app.skin', $this->skin);
		
		$this->template_dir = $this->template_dir?: [];
		
		$this->template_dir[] = __DIR__.'/UI/Templates/'. $skin;
		
		parent::__construct([
				'title' => config('epesi.app.title', 'EPESI'),
				'skin' => $skin
		]);
		
		$this->initLayout(new AdminLayout());
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
		
// 		$this->enablePjax();
		
		ob_start();
		
		$this->run();
		
		return ob_get_clean();
	}
	
	/**
	 * Initialize JS and CSS includes.
	 */
	public function initIncludes()
	{
// 		$this->requireJS(asset('js/app.js'));
// 		$this->requireCSS(asset('css/app.css'));
				
		//TODO: include below in app.js and app.css
		// jQuery
		$urlJs = $this->cdn['jquery']?? 'modules/system/js';
		$this->requireJS($urlJs.'/jquery.min.js');
		
		// Semantic UI
		$urlJs = $this->cdn['semantic-ui']?? 'modules/system/js';
		$urlCss = $this->cdn['semantic-ui']?? 'modules/system/css';
		$this->requireJS($urlJs.'/semantic.min.js');
		$this->requireCSS($urlCss.'/semantic.min.css');
		
		// Serialize Object
		$urlJs = $this->cdn['serialize-object']?? 'modules/system/js';
		$this->requireJS($urlJs.'/jquery.serialize-object.min.js');
		
		// Agile UI
		$urlJs = $this->cdn['atk']?? 'modules/system/js';
		$urlCss = $this->cdn['atk']?? 'modules/system/css';
		$this->requireJS($urlJs.'/atkjs-ui.min.js');
		$this->requireCSS($urlCss.'/agileui.css');
		
		// Draggable
		$urlJs = $this->cdn['draggable']?? 'modules/system/js';
		$this->requireJS($urlJs.'/draggable.bundle.js');
		
		// jQuery UI	
		$urlJs = $this->cdn['jquery-ui']?? 'modules/system/js';
		$this->requireJS($urlJs.'/jquery-ui.js');
		
// 		$this->requireJS('https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js');
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
	
	public function enablePjax()
	{
		// pjax library
		$this->requireJS('https://cdn.jsdelivr.net/npm/pjax/pjax.js');
		
		$this->html->template->appendHTML('HEAD', '<script>
			$(function(){
				var pjax = new Pjax({
						"elements": ".pjax", 
						"selectors": [".atk-layout", "head > script[page-pjax]", "head > title"]
				});
			});
			
		</script>');
		
		// pjax-api library
// 		$this->requireJS('https://cdn.jsdelivr.net/npm/pjax-api@latest');

// 		$this->html->template->appendHTML('HEAD', '<script>

// 			$(function(){		
// 				const { Pjax } = require("pjax-api");		
// 				new Pjax({
// 						"links": ".pjax", 
// 						"areas": [".atk-layout", "head > script[page-pjax]", "head > title"]
// 				});
// 			});
			
// 		</script>');

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
}