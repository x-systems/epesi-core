<?php

namespace Epesi\Core\System\Integration\Modules\Concerns;

trait HasMigrations
{
	use HasModule;
	
	/**
	 * Directory where module migrations are located
	 *
	 * @return string
	 */
	public static function migrations()
	{
		return implode(DIRECTORY_SEPARATOR, [static::relativePath(), 'Database', 'Migrations']);
	}
	
	final public function migrate()
	{
		$paths = $this->migrations();
		
		foreach (is_array($paths)? $paths: [$paths] as $path) {
			\Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => $path, '--force' => true]);
		}
		
		return $this;
	}
	
	final public function rollback()
	{
		$paths = $this->migrations();
		
		foreach (is_array($paths)? $paths: [$paths] as $path) {
			\Illuminate\Support\Facades\Artisan::call('migrate:rollback', ['--path' => $path]);
		}
		
		return $this;
	}
}
