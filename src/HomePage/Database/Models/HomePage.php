<?php

namespace Epesi\Core\HomePage\Database\Models;

use Epesi\Core\Data\Model;
use Epesi\Core\HomePage\HomePageSettings;
use Spatie\Permission\Models\Role;

class HomePage extends Model
{
	public $table = 'home_pages';
	
	public $caption = 'Home Page';
	
	public $title_field = 'path';
	
	function init() {
		parent::init();
		
		$this->addFields([
				[
						'path', 
						'caption' => __('Page'), 
						'values' => HomePageSettings::getAvailableHomePages(), 
						'ui'   => [
								'table' => [
										'KeyValue',
								],
						],
				],
				['role', 'caption' => __('Role'), 'enum' => Role::get()->pluck('name')->all()],
				'priority',
		]);
		
		$this->setOrder('priority');

		$this->addHook('beforeInsert', function($model, & $data) {
			$data['priority'] = $data['priority']?: $this->action('fx', ['max', 'priority'])->getOne() + 1;
		});
		
		$this->getAction('edit')->enabled = function($model) {
			return $model->id == 22; 
			};
	}
}
