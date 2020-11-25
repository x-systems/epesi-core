<?php

namespace Epesi\Core\System;

use atk4\ui\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use atk4\ui\Columns;
use atk4\ui\Header;

class SystemEnvironmentOverview extends View
{
	protected $systemRequirements = [
			'memory_limit' => '32M',
			'upload_max_filesize' => '8M',
			'post_max_size' => '16M'
	];
			
	protected $extensionRequirements = [
			'extension_loaded' => [
					'curl' => [
							'name' => 'cURL library',
							'severity' => 1
					]
			],
			'class_exists' => [
					'ZipArchive' => [
							'name' => 'ZIPArchive library',
							'severity' => 2
					]
			],
			'function_exists' => [
					'imagecreatefromjpeg' => [
							'name' => 'PHP GD extension - image processing',
							'severity' => 2
					]
			],
			'ini_get' => [
					'allow_url_fopen' => [
							'name' => 'Remote file_get_contents()',
							'severity' => 2
					]
			]
	];

	protected static function severityMap()
	{
		return [
				[
						'color' => 'green', 
						'result' => __('Good')
				], 
				[
						'color' => 'yellow', 
						'result' => __('Recommended')
				], 
				[
						'color' => 'red', 
						'result' => __('Critical')
				]
		];
	}
		
	public function renderView(): void
	{
		$this->addClass('ui grid');
		
		$columns = Columns::addTo($this);

		$this->addLegend($columns->addRow());
		
		$column = $columns->addColumn()->setStyle('min-width', '350px');
		
		$grid = View::addTo($column, ['class' => ['ui grid']]);
		
		$this->addGroupResults(__('System'), $this->testSystemCompatibility(), $grid);
		
		$this->addGroupResults(__('Extensions'), $this->testRequiredExtensions(), $grid);
		
		$column = $columns->addColumn()->setStyle('min-width', '350px');
		
		$grid = View::addTo($column, ['class' => ['ui grid']]);
		
		$this->addGroupResults(__('Database'), $this->testDatabasePermissions(), $grid);

		parent::renderView();
	}
	
	public function addLegend($container = null)
	{
		$container = $container?: $this;
		
		$legend = \atk4\ui\Header::addTo($container, [__('Scan of Environment Parameters')])->setStyle('margin-left', '2em');
		$legend = View::addTo($container)->setStyle('margin-left', 'auto');
		
		foreach (self::severityMap() as $severity) {
			\atk4\ui\Label::addTo($legend, [$severity['result'], 'class' => ["$severity[color] horizontal"]]);
		}
	}
	
	public function addGroupResults($group, $testResults = [], $container = null)
	{
		if (! $testResults) return;
		
		$container = $container?: $this;
		
		$container->add([Header::class, $group]);
		
		$severityMap = self::severityMap();
		
		foreach ($testResults as $test) {
			$color = $severityMap[$test['severity']]['color'];
			$result = $test['result']?? $severityMap[$test['severity']]['result'];

			$row = View::addTo($container, ['class' => ['row']]);
			View::addTo($row, [$test['name'], 'class' => ['nine wide column']]);
			View::addTo($row, ['class' => ['six wide right aligned column']])->add([\atk4\ui\Label::class, $result, 'class' => ["$color horizontal"]]);
		}
	}
	
	protected function testDatabasePermissions()
	{
		ob_start();
		
		@Schema::dropIfExists('test');

		@Schema::create('test', function (Blueprint $table) {
			$table->increments('id');
		});
			
		$create = Schema::hasTable('test');
				
		@Schema::table('test', function (Blueprint $table) {
			$table->addColumn('TEXT', 'field_name');
		});
		
		$alter = Schema::hasColumn('test', 'field_name');
		
		$insert = @DB::insert('INSERT INTO test (field_name) VALUES (\'test\')');
		$update = @DB::update('UPDATE test SET field_name=1 WHERE id=1');
		$delete = @DB::delete('DELETE FROM test');
		@Schema::dropIfExists('test');
		
		$drop = ! Schema::hasTable('test');
		
		ob_end_clean();
		
		$result = compact('create', 'alter', 'insert', 'update', 'delete', 'drop');
		
		array_walk($result, function(& $testResult, $testName) {
			$testResult = [
					'name' => __(':permission permission', ['permission' => strtoupper($testName)]),
					'result' => $testResult? __('OK'): __('Failed'),
					'severity' => $testResult? 0: 2
			];
		});		
		
		return $result;
	}
	
	protected function unitToInt($string)
	{
		return (int) preg_replace_callback('/(\-?\d+)(.?)/', function ($m) {
			return $m[1] * pow(1024, strpos('BKMG', $m[2]));
		}, strtoupper($string));
	}
	
	protected function testSystemCompatibility()
	{
		$ret = [];
		
		if ($requiredPhpVersion = $this->getApp()->packageInfo()['require']['php']?? null) {
			$ret[] = [
					'name' => __('PHP version required :version', ['version' => $requiredPhpVersion]),
					'result' => PHP_VERSION,
					'severity' => version_compare(PHP_VERSION, $requiredPhpVersion, '>=')? 0: 2
			];
		}
		
		foreach ($this->systemRequirements as $iniKey => $requiredSize) {
			$actualSize = ini_get($iniKey);
			$actualSizeBytes = $this->unitToInt($actualSize);
			$requiredSizeBytes = $this->unitToInt($requiredSize);
			
			$severity = 0;
			if ($actualSizeBytes < $requiredSizeBytes) {
				$severity = 2;
			}
			elseif ($actualSizeBytes == $requiredSizeBytes) {
				$severity = 1;
			}
			
			$ret[] = [
					'name' => ucwords(str_ireplace('_', ' ', $iniKey)) . ' > ' . $requiredSize,
					'result' => $actualSize,
					'severity' => $severity
			];
		}
		
		return $ret;
	}
	
	protected function testRequiredExtensions()
	{
		$ret = [];
		
		foreach ($this->extensionRequirements as $callback => $extensions) {
			foreach ($extensions as $extension => $test) {
				$result = $callback($extension);
				
				$ret[] = array_merge($test, [
						'result' => $result? __('OK'): __('Missing'),
						'severity' => $result? 0: $test['severity']
				]);
			}
		}
		
		return $ret;
	}
	
}
