<?php

namespace Epesi\Core\System\User\Database\Models\atk4;

use Epesi\Core\Data\Model;

class User extends Model
{
	public $table = 'users';
	
	function init(){
		parent::init();
		
		$this->addFields(['name']);
	}
}
