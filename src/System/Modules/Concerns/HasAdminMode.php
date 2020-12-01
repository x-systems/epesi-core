<?php

namespace Epesi\Core\System\Modules\Concerns;

trait HasAdminMode
{
	final public function setAdminMode() {
		$this->stickyGet('admin', 1);
		
		return $this;
	}
	
	abstract public function stickyGet(string $name, ?string $newValue = null): ?string;
}
