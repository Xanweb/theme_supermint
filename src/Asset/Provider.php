<?php
namespace Concrete\Package\ThemeSupermint\Asset;

use Xanweb\Module\Asset\Provider as AssetProvider;

class Provider extends AssetProvider
{

    public function getAssets(): array
    {
        return [
            'mmenu' => [
                ['javascript', 'js/build/jquery.mmenu.min.all.js', ['version' => '5.4.2'], $this->pkg],
                ['css', 'themes/supermint/css/addons/jquery.mmenu.all.css', ['version' => '5.4.2'], $this->pkg],
            ],
            'boxnav' => [
                ['javascript', 'js/build/jquery.boxnav.js', ['version' => '1.0'], $this->pkg],
            ],
            'slick' => [
                ['javascript', 'js/build/slick.min.js', ['version' => '1.5.0'], $this->pkg],
                ['css', 'themes/supermint/css/addons/slick.css', ['version' => '1.5.0'], $this->pkg],
            ],
            'fitvids' => [
                ['javascript', 'js/build/jquery.fitvids.js', ['version' => '1.0'], $this->pkg],
            ],
            'rcrumbs' => [
                ['javascript', 'js/build/jquery.rcrumbs.min.js', ['version' => '1.1'], $this->pkg],
            ],
            'nprogress' => [
                ['javascript', 'js/build/nprogress.js', ['version' => '0.1.6'], $this->pkg],
            ],
            'autohidingnavbar' => [
                ['javascript', 'js/build/jquery.autohidingnavbar.js', ['version' => '0.1.6'], $this->pkg],
            ],
            'supermint.script' => [
                ['javascript', 'js/build/script.js', ['version' => '0.1.6'], $this->pkg],
            ],
            'YTPlayer' => [
                ['javascript', 'js/build/jquery.mb.YTPlayer.min.js', ['version' => '2.7.5'], $this->pkg],
                ['css', 'themes/supermint/css/addons/YTPlayer.css', ['version' => '2.7.5'], $this->pkg],
            ],
            'modernizr.custom' => [
                ['javascript', 'js/build/modernizr.custom.js', ['version' => '2.7.1'], $this->pkg],
            ],
            'transit' => [
                ['javascript', 'js/build/jquery.transit.js', ['version' => '0.1'], $this->pkg],
                ['css', 'themes/supermint/css/addons/jquery.transit.css', ['version' => '0.1'], $this->pkg],
            ],
            'imageloaded' => [
                ['javascript', 'js/build/imageloaded.js', ['version' => '2.1.1'], $this->pkg],
            ],
            'isotope' => [
                ['javascript', 'js/build/isotope.pkgd.min.js', ['version' => '2.1.1'], $this->pkg],
            ],
            'wow' => [
                ['javascript', 'js/build/wow.js', ['version' => '1.1.2'], $this->pkg],
            ],
            'harmonize-text' => [
                ['javascript', 'js/build/harmonize-text.js', ['version' => '1'], $this->pkg],
            ],
            'enquire' => [
                ['javascript', 'js/build/enquire.js', ['version' => '2.1.2'], $this->pkg],
            ],
            'twitterFetcher' => [
                ['javascript', 'js/build/twitterFetcher_min.js', ['version' => '12'], $this->pkg],
            ],
            'element-masonry' => [
                ['javascript', 'js/build/element-masonry.js', ['version' => '1'], $this->pkg],
            ],
            'slick-theme' => [
                ['css', 'themes/supermint/css/addons/slick-theme.css', ['version' => '1.5.0'], $this->pkg],
            ],
            'bootsrap-custom' => [
                ['css', 'themes/supermint/css/addons/bootstrap.custom.min.css', ['version' => '3.3.4'], $this->pkg],
            ],
            'animate' => [
                ['css', 'themes/supermint/css/addons/animate.css', ['version' => '1'], $this->pkg],
            ],
            'mega-menu' => [
                ['css', 'themes/supermint/css/addons/mega-menu.css', ['version' => '1.1.0'], $this->pkg],
            ],
        ];
    }
}