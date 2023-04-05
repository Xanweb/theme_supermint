<?php 
namespace Concrete\Package\ThemeSupermint;

use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\File\Image\Thumbnail\Type\Type as ThumbnailType;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Route;
use Concrete\Package\ThemeSupermint\Module\Module;
use Doctrine\ORM\EntityManagerInterface;
use Events;
use URL;
use Core;
use Concrete\Package\ThemeSupermint\Models\ThemeSupermintOptions;
use Concrete\Package\ThemeSupermint\Helper\Upgrade;
use Concrete\Core\Editor\Plugin;
use PageType;
use Concrete\Core\Backup\ContentImporter;
use FileList;
use PageList;
use StackList;
use Concrete\Core\StyleCustomizer\Style\ValueList;
use Xanweb\Module\Installer;
use Xanweb\Module\Uninstaller;

class Controller extends Package  {

	protected $pkgHandle = 'theme_supermint';
    protected $themeHandle = 'supermint';
    protected $appVersionRequired = '9.0';
    protected $pkgVersion = '3.9';
    protected $pkgAllowsFullContentSwap = true;
    protected $startingPoint;

    public function getContentSwapFiles(): array
    {
        return parent::getContentSwapFiles();
    }

    public function getPackageName()
    {
        return t("Supermint Theme");
    }

	public function getPackageDescription()
    {
		return t("Supermint responsive suit any kind of website.");
	}

	public function install($data = [])
    {
        $this->startingPoint = $data['spHandle'];

        if ($data['pkgDoFullContentSwap'] === '1' && $this->startingPoint === '0') {
            throw new \Exception(t('You must choose a Starting point to Swap all content'));
        }

        $pkg = parent::install();

        $theme = PageTheme::add('supermint', $pkg);
        $theme->applyToSite();

        // Theme options
		$o = $this->app->make(ThemeSupermintOptions::class);
		$o->installDB($this->startingPoint);

		// Setting up the editor clips
        $config = $this->app->make('config');
		$plugins = (array) $config->get('concrete.editor.plugins.selected', []);
		$plugins = array_unique(array_merge(['themefontcolor', 'themeclips'],$plugins));
        $config->save('concrete.editor.plugins.selected', $plugins);
        // Elements installing
        $this->installOrUpgrade($pkg);

	}

	private function installThumbnailTypes(array ...$thumbnailTypes)
    {
        $em = $this->app->make(EntityManagerInterface::class);
        $repo = $em->getRepository(ThumbnailType::class);

        foreach ($thumbnailTypes as $thumbnail) {
            $handle = (string) $thumbnail['handle'];
            if ($repo->findOneBy(['ftTypeHandle' => $handle]) !== null) {
                continue;
            }

            $type = new ThumbnailType();
            $type->setName((string) $thumbnail['name']);
            $type->setHandle((string) $thumbnail['handle']);
            if (isset($thumbnail['sizingMode'])) {
                $type->setSizingMode((string) $thumbnail['sizingMode']);
            }
            $type->setIsUpscalingEnabled(isset($thumbnail['upscalingEnabled']) && $thumbnail['upscalingEnabled']);
            $type->setKeepAnimations(isset($thumbnail['keepAnimations']) && $thumbnail['keepAnimations']);
            if (isset($thumbnail['width'])) {
                $type->setWidth((string) $thumbnail['width']);
            }
            if (isset($thumbnail['height'])) {
                $type->setHeight((string) $thumbnail['height']);
            }
            if (isset($thumbnail['required'])) {
                $required = (string) $thumbnail['required'];
                if ($required) {
                    $type->requireType();
                }
            }
            if (isset($thumbnail['limitedToFileSets'])) {
                $type->setLimitedToFileSets((bool) (string) $thumbnail['limitedToFileSets']);
            }
            $em->persist($type);
        }
        $em->flush();
    }

	private function installOrUpgrade($pkg)
    {
        $installer = new  Installer($pkg);
        $installer->installSinglePages(
            ['/dashboard/supermint_options', t('Supermint options')],
            ['/dashboard/supermint_options/theme_options', t('Theme options')],
            ['/dashboard/supermint_options/sliders', t('Sliders')],
            ['/dashboard/supermint_options/fonts', t('Fonts')],
            ['/dashboard/supermint_options/site_settings', t('Site Settings')],
            ['/dashboard/supermint_options/options_presets', t('Presets')],
        );

        $installer->installBlockTypes('pie_chart');

        $this->installThumbnailTypes(
            ['handle' => 'tiny', 'name' => 'Tiny Image', 'width' => '390'],
            ['handle' => 'small', 'name' => 'Small Image', 'width' => '740'],
            ['handle' => 'medium', 'name' => 'Medium Image', 'width' => '940'],
            ['handle' => 'large', 'name' => 'Large Image', 'width' => '1140']
        );

        $ci = new ContentImporter();
        $ci->importContentFile($this->getPackagePath() . '/config/install/base/page_templates.xml');
        $ci->importContentFile($this->getPackagePath() . '/config/install/base/attributes.xml');
	}

	public function uninstall()
    {
        parent::uninstall();
        Uninstaller::dropTables('SupermintOptions', 'SupermintOptionsPreset');
	}

	public function upgrade()
    {
        //$o = $this->app->make(ThemeSupermintOptions::class);
        //$o->updateDB();
        parent::upgrade();
        $this->installOrUpgrade($this->getPackageEntity());
	}

	public function on_start() {
        Module::boot();
        $this->registerRoutes();
        $this->registerAssets();
        $this->registerEvents();
    }

    function registerEvents () {
        Events::addListener(
            'on_before_render',
            function($e) {
                $session = \Core::make('session');
                $c = Page::getCurrentPage();
                // Register options into the session
                $themeSupermintOptions = $this->app->make(ThemeSupermintOptions::class);
                $options = $themeSupermintOptions->getOptionsFromActivePresetID();
                $session->set('supermint.options',$options);
            });
    }

    public function registerAssets () {
 		$al = AssetList::getInstance();
		// -- Redactor Plugins -- \\

        $pluginManager = Core::make('editor')->getPluginManager();
		// ThemeFont plugin
        $al->register('javascript', 'editor/plugin/themefontcolor', 'js/editor/themefontcolor.js', array(), $this);
        $al->register('css', 'editor/plugin/themefontcolor', 'css/editor/themefontcolor.css', array(), $this);
        $al->registerGroup('editor/plugin/themefontcolor', array(
            array('javascript', 'editor/plugin/themefontcolor'),
            array('css', 'editor/plugin/themefontcolor')
            ));

        $plugin = new Plugin();
        $plugin->setKey('themefontcolor');
        $plugin->setName('Font colors from theme');
        $plugin->requireAsset('editor/plugin/themefontcolor');

        $pluginManager->register($plugin);
		// themClips plugin
        $al->register('javascript', 'editor/plugin/themeclips', 'js/editor/themeclips.js', array(), $this);
        $al->register( 'javascript', 'chosen-icon', 'js/chosenIcon.jquery.js',  array(), 'theme_supermint' );
        $al->register( 'javascript', 'chosen.jquery.min', 'js/chosen.jquery.min.js',  array(), 'theme_supermint' );
        $al->register( 'css', 'chosenicon', 'css/chosenicon.css',  array(), 'theme_supermint' );
        $al->register( 'css', 'chosen.min', 'css/chosen.min.css', array(), 'theme_supermint' );

        $al->registerGroup('editor/plugin/themeclips', array(
            array('javascript', 'editor/plugin/themeclips'),
            array('javascript', 'chosen-icon'),
            array('javascript', 'chosen.jquery.min'),
            array('css', 'chosen.min'),
            array('css', 'chosenicon')
            ));

        $plugin = new Plugin();
        $plugin->setKey('themeclips');
        $plugin->setName('Snippets from Supermint');
        $plugin->requireAsset('editor/plugin/themeclips');

        $pluginManager->register($plugin);

	}


    public function registerRoutes() {
        Route::register(
            '/ThemeSupermint/tools/extend.js',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\ExtendJs::render'
        );
        Route::register(
            '/ThemeSupermint/tools/get_preset_colors',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\PresetColors::getColors'
        );
        Route::register(
            '/ThemeSupermint/tools/font_details',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\FontsTools::getFontDetails'
        );
        Route::register(
            '/ThemeSupermint/tools/font_url',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\FontsTools::getFontsURL'
        );
        Route::register(
            '/ThemeSupermint/tools/font_url_ajax',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\FontsTools::getFontURLAjax'
        );
        Route::register(
            '/ThemeSupermint/tools/override',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\OverrideCss::render'
        );
        Route::register(
            '/ThemeSupermint/tools/xml_preset',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\XmlPreset::render'
        );
        Route::register(
            '/ThemeSupermint/tools/get_awesome_icons',
            '\Concrete\Package\ThemeSupermint\Controller\Tools\AwesomeArray::getAwesomeArray'
        );
    }

    public function swapContent($options) {

        if ($this->validateClearSiteContents($options)) {
            \Core::make('cache/request')->disable();

            $pl = new PageList();
            $pages = $pl->getResults();
            foreach ($pages as $c) $c->delete();

            $fl = new FileList();
            $files = $fl->getResults();
            foreach ($files as $f) $f->delete();

            // clear stacks
            $sl = new StackList();
            foreach ($sl->get() as $c) $c->delete();

            $home = Page::getByID(HOME_CID);
            $blocks = $home->getBlocks();
            foreach ($blocks as $b) $b->deleteBlock();

            $pageTypes = PageType::getList();
            foreach ($pageTypes as $ct) $ct->delete();

						$startingPointFolder = $this->getPackagePath() . '/starting_points/'. $this->startingPoint;

            // Import Files
            if (is_dir($startingPointFolder . '/content_files')) {
                $ch = new ContentImporter();
                $computeThumbnails = true;
                if ($this->contentProvidesFileThumbnails()) $computeThumbnails = false;
                $ch->importFiles($startingPointFolder . '/content_files', true );
            }

            // Install the starting point.
            if (is_file($startingPointFolder . '/content.xml')) :
                $ci = new ContentImporter();
                $ci->importContentFile($startingPointFolder . '/content.xml');
            endif;

            // Set it as default for the page theme
            $this->setPresetAsDefault($this->startingPoint);

            // Restore Cache
            \Core::make('cache/request')->enable();
        }
    }

    function setPresetAsDefault ($presetHandle) {
        $outputError = false;
        $baseExceptionText = t('The theme and the Starting point has been installed correctly but it\'s ');
        $pt = PageTheme::getByHandle($this->themeHandle);
        $preset = $pt->getThemeCustomizablePreset($presetHandle);
        if (!is_object($preset)) {
            if($outputError) throw new \Exception($baseExceptionText . t('impossible to retrieve the Preset selected : ' . $presetHandle));
            return;
        }
        $styleList = $pt->getThemeCustomizableStyleList();
        if (!is_object($styleList)) {
            if($outputError) throw new \Exception($baseExceptionText . t('impossible to retrieve the Style List from ' . $presetHandle));
            return;
        }
        $valueList = $preset->getStyleValueList();
        $vl = new ValueList();

        $sets = $styleList->getSets();
        if (!is_array($sets)) {
            if($outputError) throw new \Exception($baseExceptionText . t('impossible to retrieve the Style Set from ' . $presetHandle));
            return;
        }

        foreach ($sets as $set) :
         foreach($set->getStyles() as $style)  :
            $valueObject = $style->getValueFromList($valueList);
            if (is_object($valueObject))
                $vl->addValue($valueObject);
         endforeach;
        endforeach;

        $vl->save();
        $pt->setCustomStyleObject($vl, $preset);
    }
}
