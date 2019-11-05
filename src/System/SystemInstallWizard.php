<?php

namespace Epesi\Core\System;

use Epesi\Core\System\Seeds\Form;
use atk4\ui\jsExpression;
use atk4\ui\Wizard;
use Illuminate\Support\Facades\Artisan;
use Epesi\Core\System\Integration\Modules\Concerns\HasAdminMode;
use Illuminate\Support\Facades\App;
use Epesi\Core\System\Integration\Modules\ModuleManager;

class SystemInstallWizard extends Wizard
{
	use HasAdminMode;
	
	public function init()
	{
		parent::init();

		$this->setAdminMode()->performInstallationSteps();
	}
	
	public function performInstallationSteps()
	{
		$this->addStep(__('Welcome'), [__CLASS__, 'stepWelcome']);
		
		$this->addStep([__('License'), 'icon'=>'id card', 'description'=>__('Accept license conditions')], [__CLASS__, 'stepLicense']);
			
		$this->addStep([__('Database'), 'icon'=>'database', 'description'=>__('Database connection settings')], [__CLASS__, 'stepDatabase']);
		
		$this->addStep([__('Environment'), 'icon'=>'configure', 'description'=>__('Check environment')], [__CLASS__, 'stepEnvironment']);
		
		$this->addFinish([__CLASS__, 'stepInstallationCompleted']);
		
	}
	
	public function addRequiredNote()
	{
		$this->add(['View', __('denotes required field'), 'class' => ['required-note']])->setStyle(['float' => 'right']);
		
		eval_css('
			.required-note::before {
				margin: 0 .2em 0 0;
				content: \'*\';
				color: #db2828;
			}
		');
	}
	
	public function setForm($form)
	{
		$this->buttonNext->on('click', $form->js()->submit());
	}
	
	public static function stepWelcome($wizard)
	{
		$columns = $wizard->add('Columns');
		
		$column = $columns->addColumn();
		$column->add(['Message', __('Thank you for downloading EPESI!')])->text
		->addParagraph(__('This wizard will guide you though the process of setting up your new CRM / ERP installation'))
		->addParagraph(__('Select the installation language and click NEXT button to proceed to next step'));
		
		$column = $columns->addColumn();

		if (! function_exists('locale_get_display_language')) {
			$column->addClass('middle aligned');
			$column->add(['Label', __('Install php-intl extension to enable language selection!'), 'class' => ['red']]);
			
			return;
		}

		$systemLanguages = app()->make(\JoeDixon\Translation\Drivers\Translation::class)->allLanguages()->toArray();

		$values = array_intersect_key(self::getDisplayLanguages(), $systemLanguages);
		
		$form = $column->add(new Form());
		
		$form->addField('language', ['DropDown', 'values' => $values, 'caption' => __('Select Language'), 'iconLeft' => 'globe'], ['required'=>true])->set($wizard->recall('language', 'en'));
		
		$form->onSubmit(function ($form) use ($wizard) {
			$wizard->memorize('language', $form->model['language']);
			
			App::setLocale($form->model['language']);
			
			return $wizard->jsNext();
		});

		$wizard->setForm($form);
	}
	
	public static function stepLicense($wizard)
	{
		$columns = $wizard->add('Columns');
		$column = $columns->addColumn();
		
		$license = $column->add(['View', 'defaultTemplate' => 'license.html'])->setStyle(['max-height' => '500px', 'overflow' => 'auto', 'padding' => '5px']);
		
		$license->js(true)->niceScroll();
		
		$license->template->setHTML('epesi', config('epesi.app.title'));
		
		$license->template->setHTML('copyright', config('epesi.app.copyright'));
		
		$column = $columns->addColumn();
		
		$form = $column->add(new Form());
		$form->addField('copyright', ['Checkbox', 'caption' => __('I will not remove the Copyright notice as required by the MIT license.')], ['required'=>true]);
		$form->addField('logo', ['Checkbox', 'caption' => __('I will not remove ":epesi powered" logo and the link from the application login screen or the toolbar.', ['epesi' => config('epesi.app.title')])], ['required'=>true]);
		$form->addField('support', ['Checkbox', 'caption' => __('I will not remove "Support -> About" credit page from the application menu.')], ['required'=>true]);
		$form->addField('store', ['Checkbox', 'caption' => __('I will not remove or rename ":epesi Store" links from the application.', ['epesi' => config('epesi.app.title')])], ['required'=>true]);
		
		$form->onSubmit(function ($form) use ($wizard) {
			return $wizard->jsNext();
		});
			
		$wizard->setForm($form);
	}
	
	public static function stepDatabase($wizard)
	{
		$wizard->addRequiredNote();
		
		$form = $wizard->add('Form');
		
		$form->addField('host', __('Database Host'), ['required'=>true])->placeholder = __('e.g. localhost');
		$form->addField('port', __('Database Port'));
		
		$form->addField('driver', ['DropDown', 'values' => [
				'mysql' => 'MySQL',
				'postgre' => 'PostgeSQL',
		], 'caption' => __('Database Engine')], ['required'=>true]);
		
		$form->addField('database', __('Database Name'));
		$form->addField('username', __('Database Server User'), ['required'=>true]);
		$form->addField('password', ['Password', 'caption' => __('Database Server Password')], ['required'=>true]);
		
		$form->addField('create', ['Checkbox', 'caption' => __('Create New Database')])->on('change', new jsExpression('if ($(event.target).is(":checked")) alert([])', [__('WARNING: Make sure you have CREATE access level to do this!')]));

		foreach ($wizard->recall('connection', []) as $name => $value) {
			if (! $field = $form->fields[$name]?? null) continue;
			
			$field->set($value);
		}

		$form->onSubmit(function ($form) use ($wizard) {
			$connection = $form->model->get();

			$wizard->memorize('connection', $connection);

			Artisan::call('epesi:database-connection', ['--connection' => $connection]);
		
			if ($connection['create']) {
				Artisan::call('epesi:database-create', ['name' => $connection['database'], '--connection' => $connection]);
			}
			
			ModuleManager::clearCache();
			
			Artisan::call('config:clear');
			Artisan::call('cache:clear');

			return $wizard->jsNext();
		});
	}
	
	public static function stepEnvironment($wizard)
	{
		Artisan::call('migrate');
		
		ob_start();
		ModuleManager::install('system');
		ob_end_clean();
		
		$wizard->add(new SystemEnvironmentOverview());
	}
	
	public static function stepInstallationCompleted($wizard)
	{
		$wizard->add(['Header', __(':epesi was successfully installed!', ['epesi' => config('epesi.app.title')]), 'huge centered']);
	}

	public static function getDisplayLanguages() {
		return [
				'aa' => __('Afar'),
				'ab' => __('Abkhazian'),
				'ae' => __('Avestan'),
				'af' => __('Afrikaans'),
				'ak' => __('Akan'),
				'am' => __('Amharic'),
				'an' => __('Aragonese'),
				'ar' => __('Arabic'),
				'as' => __('Assamese'),
				'av' => __('Avaric'),
				'ay' => __('Aymara'),
				'az' => __('Azerbaijani'),
				'ba' => __('Bashkir'),
				'be' => __('Belarusian'),
				'bg' => __('Bulgarian'),
				'bh' => __('Bihari'),
				'bi' => __('Bislama'),
				'bm' => __('Bambara'),
				'bn' => __('Bengali'),
				'bo' => __('Tibetan'),
				'br' => __('Breton'),
				'bs' => __('Bosnian'),
				'ca' => __('Catalan'),
				'ce' => __('Chechen'),
				'ch' => __('Chamorro'),
				'co' => __('Corsican'),
				'cr' => __('Cree'),
				'cs' => __('Czech'),
				'cu' => __('Church Slavic'),
				'cv' => __('Chuvash'),
				'cy' => __('Welsh'),
				'da' => __('Danish'),
				'de' => __('German'),
				'dv' => __('Divehi'),
				'dz' => __('Dzongkha'),
				'ee' => __('Ewe'),
				'el' => __('Greek'),
				'en' => __('English'),
				'eo' => __('Esperanto'),
				'es' => __('Spanish'),
				'et' => __('Estonian'),
				'eu' => __('Basque'),
				'fa' => __('Persian'),
				'ff' => __('Fulah'),
				'fi' => __('Finnish'),
				'fj' => __('Fijian'),
				'fo' => __('Faroese'),
				'fr' => __('French'),
				'fy' => __('Western Frisian'),
				'ga' => __('Irish'),
				'gd' => __('Scottish Gaelic'),
				'gl' => __('Galician'),
				'gn' => __('Guarani'),
				'gu' => __('Gujarati'),
				'gv' => __('Manx'),
				'ha' => __('Hausa'),
				'he' => __('Hebrew'),
				'hi' => __('Hindi'),
				'ho' => __('Hiri Motu'),
				'hr' => __('Croatian'),
				'ht' => __('Haitian'),
				'hu' => __('Hungarian'),
				'hy' => __('Armenian'),
				'hz' => __('Herero'),
				'ia' => __('Interlingua (International Auxiliary Language Association)'),
				'id' => __('Indonesian'),
				'ie' => __('Interlingue'),
				'ig' => __('Igbo'),
				'ii' => __('Sichuan Yi'),
				'ik' => __('Inupiaq'),
				'io' => __('Ido'),
				'is' => __('Icelandic'),
				'it' => __('Italian'),
				'iu' => __('Inuktitut'),
				'ja' => __('Japanese'),
				'jv' => __('Javanese'),
				'ka' => __('Georgian'),
				'kg' => __('Kongo'),
				'ki' => __('Kikuyu'),
				'kj' => __('Kwanyama'),
				'kk' => __('Kazakh'),
				'kl' => __('Kalaallisut'),
				'km' => __('Khmer'),
				'kn' => __('Kannada'),
				'ko' => __('Korean'),
				'kr' => __('Kanuri'),
				'ks' => __('Kashmiri'),
				'ku' => __('Kurdish'),
				'kv' => __('Komi'),
				'kw' => __('Cornish'),
				'ky' => __('Kirghiz'),
				'la' => __('Latin'),
				'lb' => __('Luxembourgish'),
				'lg' => __('Ganda'),
				'li' => __('Limburgish'),
				'ln' => __('Lingala'),
				'lo' => __('Lao'),
				'lt' => __('Lithuanian'),
				'lu' => __('Luba-Katanga'),
				'lv' => __('Latvian'),
				'mg' => __('Malagasy'),
				'mh' => __('Marshallese'),
				'mi' => __('Maori'),
				'mk' => __('Macedonian'),
				'ml' => __('Malayalam'),
				'mn' => __('Mongolian'),
				'mr' => __('Marathi'),
				'ms' => __('Malay'),
				'mt' => __('Maltese'),
				'my' => __('Burmese'),
				'na' => __('Nauru'),
				'nb' => __('Norwegian Bokmal'),
				'nd' => __('North Ndebele'),
				'ne' => __('Nepali'),
				'ng' => __('Ndonga'),
				'nl' => __('Dutch'),
				'nn' => __('Norwegian Nynorsk'),
				'no' => __('Norwegian'),
				'nr' => __('South Ndebele'),
				'nv' => __('Navajo'),
				'ny' => __('Chichewa'),
				'oc' => __('Occitan'),
				'oj' => __('Ojibwa'),
				'om' => __('Oromo'),
				'or' => __('Oriya'),
				'os' => __('Ossetian'),
				'pa' => __('Panjabi'),
				'pi' => __('Pali'),
				'pl' => __('Polish'),
				'ps' => __('Pashto'),
				'pt' => __('Portuguese'),
				'qu' => __('Quechua'),
				'rm' => __('Raeto-Romance'),
				'rn' => __('Kirundi'),
				'ro' => __('Romanian'),
				'ru' => __('Russian'),
				'rw' => __('Kinyarwanda'),
				'sa' => __('Sanskrit'),
				'sc' => __('Sardinian'),
				'sd' => __('Sindhi'),
				'se' => __('Northern Sami'),
				'sg' => __('Sango'),
				'si' => __('Sinhala'),
				'sk' => __('Slovak'),
				'sl' => __('Slovenian'),
				'sm' => __('Samoan'),
				'sn' => __('Shona'),
				'so' => __('Somali'),
				'sq' => __('Albanian'),
				'sr' => __('Serbian'),
				'ss' => __('Swati'),
				'st' => __('Southern Sotho'),
				'su' => __('Sundanese'),
				'sv' => __('Swedish'),
				'sw' => __('Swahili'),
				'ta' => __('Tamil'),
				'te' => __('Telugu'),
				'tg' => __('Tajik'),
				'th' => __('Thai'),
				'ti' => __('Tigrinya'),
				'tk' => __('Turkmen'),
				'tl' => __('Tagalog'),
				'tn' => __('Tswana'),
				'to' => __('Tonga'),
				'tr' => __('Turkish'),
				'ts' => __('Tsonga'),
				'tt' => __('Tatar'),
				'tw' => __('Twi'),
				'ty' => __('Tahitian'),
				'ug' => __('Uighur'),
				'uk' => __('Ukrainian'),
				'ur' => __('Urdu'),
				'uz' => __('Uzbek'),
				've' => __('Venda'),
				'vi' => __('Vietnamese'),
				'vo' => __('Volapuk'),
				'wa' => __('Walloon'),
				'wo' => __('Wolof'),
				'xh' => __('Xhosa'),
				'yi' => __('Yiddish'),
				'yo' => __('Yoruba'),
				'za' => __('Zhuang'),
				'zh' => __('Chinese'),
				'zu' => __('Zulu')
		];
	}
}
