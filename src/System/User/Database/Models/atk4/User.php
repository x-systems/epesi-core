<?php

namespace Epesi\Core\System\User\Database\Models\atk4;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;

class User extends Model
{
    use HasEpesiConnection;
    
	public $table = 'users';
	
	protected function init(): void
	{
		parent::init();
		
		$this->addFields(['name']);
	}
}
