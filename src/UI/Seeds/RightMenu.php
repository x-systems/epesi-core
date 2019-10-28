<?php 

namespace Epesi\Core\UI\Seeds;

use atk4\ui\Menu as BaseMenu;
use atk4\ui\FormField\Input;
use Illuminate\Support\Facades\URL;
use Epesi\Core\Integration\Joints\UserMenuJoint;
use Illuminate\Support\Facades\Auth;

class RightMenu extends BaseMenu
{
	protected $entries;
	protected $tools;
	
	protected $userMenu;
	
	protected $joint = UserMenuJoint::class;
	
	public function init()
	{		
		parent::init();

		$this->entries = collect();
		
		$this->tools = collect();
		
		$this->addItem()->add(new Input([
				'placeholder' => 'Search',
				'icon' => 'search'
		]))->addClass('transparent');
		
		// $messageMenu = $this->menuRight->addMenu(['', 'icon' => 'envelope outline']);
		
		$this->addItem([
				'icon' => 'th'
		], new LaunchPad($this))->setAttr([
				'title' => __('Launchpad'),
		]);

		foreach(UserMenuJoint::collect() as $joint) {
			foreach ($joint->tools()?: [] as $tool) {
				$this->addTool($tool);
			}

			foreach ($joint->entries()?: [] as $entry) {
				$this->addEntry($entry);
			}			
		}
		
		$this->userMenu = $this->addMenu([
				Auth::user()->name . ' as Team A',
				'icon' => 'user'
		]);
		
		$this->addEntry(['Perspective', 'icon' => 'users']);
		$this->addEntry(['My Contact', 'icon' => 'contact']);
		$this->addEntry(['My Company', 'icon' => 'users']);
		
		$this->addEntry([
				'item' => ['Logout', 'icon' => 'sign out', 'attr' => ['onclick' => "event.preventDefault();$('#logout-form').submit();"]],
				'action' => url('logout'), 
				'group' => '10000:user',
				'callback' => function ($item){
					$logoutForm = $item->add(['View', 'attr' => ['method' => 'POST', 'action' => URL::to('logout')]])->setElement('form')->addStyle(['display' => 'none']);
					$logoutForm->id = 'logout-form';
					$logoutForm->add(['View', 'attr' => ['type' => 'hidden', 'name' => '_token', 'value' => csrf_token()]])->setElement('input');
				},
		]);
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
	
	public function renderView()
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