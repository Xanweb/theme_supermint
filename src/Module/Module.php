<?php
namespace Concrete\Package\ThemeSupermint\Module;

use Concrete\Package\ThemeSupermint\Asset\Provider as AssetProvider;
use Xanweb\Module\Module as AbstractModule;

class Module extends AbstractModule
{

    public static function pkgHandle(): string
    {
        return 'theme_supermint';
    }

    /**
     * {@inheritdoc}
     *
     * @see AbstractModule::getAssetProviders()
     */
    protected static function getAssetProviders(): array
    {
        return [
            AssetProvider::class
        ];
    }
}