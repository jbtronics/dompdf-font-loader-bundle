<?php

namespace Jbtronics\DompdfFontLoaderBundle\CacheWarmer;

use Jbtronics\DompdfFontLoaderBundle\Services\ConfiguredFontsInstaller;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class DompdfFontLoaderCacheWarmer implements CacheWarmerInterface
{
    public function __construct(private bool $isEnabled, private ConfiguredFontsInstaller $configuredFontsInstaller)
    {

    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        if (!$this->isEnabled) {
            return [];
        }

        //If the cache warmer is enabled, install the fonts configured in the bundle configuration
        $this->configuredFontsInstaller->installConfigured();

        // No need to preload anything
        return [];
    }
}
