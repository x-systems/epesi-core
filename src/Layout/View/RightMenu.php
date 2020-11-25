<?php 

namespace Epesi\Core\Layout\View;

use atk4\ui\Menu as BaseMenu;
use atk4\ui\Form\Control\Input;
use Epesi\Core\Layout\Integration\Joints\UserMenuJoint;
use Illuminate\Support\Facades\Auth;
use atk4\ui\View;
use atk4\ui\VirtualPage;
use Epesi\Core\System\View\LaunchButton;
use atk4\ui\JsModal;
use Illuminate\Support\Facades\URL;

class RightMenu extends BaseMenu
{
	protected $entries;
	protected $tools;
	
	protected $userMenu;
	
	protected $userMenuLabel;
	
	protected function init(): void
	{		
		parent::init();

		$this->entries = collect();
		
		$this->tools = collect();
		
		// credits
		$this->addItem(__(':epesi powered version :version', [
				'epesi' => config('epesi.ui.credit.title', 'EPESI'), 
				'version' => $this->getApp()->version
		]))->setStyle('font-size', '80%')->link(config('epesi.ui.credit.link'));
		
		// global search
		$this->addItem()->add(new Input([
				'placeholder' => 'Search ' . config('epesi.ui.title', 'EPESI'),
				'icon' => 'search'
		]))->addClass('transparent');
		
		// $messageMenu = $this->menuRight->addMenu(['', 'icon' => 'envelope outline']);
		
		$this->addItem([
				'icon' => 'th'
		], Launchpad::addTo($this)->getJsModal());
		
		foreach(UserMenuJoint::collect() as $joint) {
			foreach ($joint->tools()?: [] as $tool) {
				$this->addTool($tool);
			}

			foreach ($joint->entries()?: [] as $entry) {
				$this->addEntry($entry);
			}			
		}

		$this->userMenu = $this->addMenu([
				$this->getUserMenuLabel(),
				'icon' => 'user'
		]);
		
		$this->addEntry(['Perspective', 'icon' => 'users']);
		$this->addEntry(['My Contact', 'icon' => 'contact']);
		$this->addEntry(['My Company', 'icon' => 'users']);
		
		$this->addEntry([
				'item' => ['Logout', 'icon' => 'sign out', 'attr' => ['onclick' => "event.preventDefault();$('#logout-form').submit();"]],
				'action' => url('logout'), 
				'group' => '10000:user',
				'callback' => function ($item) {
					$logoutForm = View::addTo($item, [
							'id' => 'logout-form', 
							'attr' => [
									'method' => 'POST', 
									'action' => url('logout'),
							],
					])->setElement('form')->addStyle(['display' => 'none']);

					View::addTo($logoutForm, [
							'attr' => [
									'type' => 'hidden', 
									'name' => '_token', 
									'value' => csrf_token(),
							],
					])->setElement('input');
				},
		]);
	}

	public function setUserMenuLabel($label)
	{
		$this->userMenuLabel = $label;
		
		return $this;
	}
	
	public function getUserMenuLabel()
	{
		return $this->userMenuLabel?: Auth::user()->name;
	}
	
	public function addEntry($entry)
	{
		$entry = collect(array_merge(['item' => $entry, 'group' => '00500:general', 'weight' => 10], $entry));
		
		if (! $entry->get('item')) return;
		
		$this->entries->add($entry);
	}
	
	public function addTool($tool)
	{
		
	}
	
	public function renderView(): void
	{
		$this->addRegisteredEntries();
		
		parent::renderView();
	}
	
	protected function addRegisteredEntries()
	{
		$empty = true;
		foreach ($this->entries->groupBy('group')->sortKeys() as $group) {
			if (!$empty) $this->userMenu->addDivider();
		
			foreach ($group->sortBy('weight') as $entry) {
				$empty = false;
				
				$item = $this->userMenu->addItem($entry['item'], $entry->get('action'));
				
				if (! $callback = $entry->get('callback')) continue;
					
				$callback($item);
			}
		}
	}
}