<?php

namespace Jbtronics\DompdfFontLoaderBundle\Services;

class ConfiguredFontsInstaller
{
    public function __construct(
        private DompdfFactoryInterface $dompdfFactory,
        private DompdfFontLoader $fontLoader,
        private array $fonts,
        private array $autodiscoveryPaths,
        private array $autodiscoveryExcludePatterns,
        private string $autodiscoveryFilePattern
    ) {

    }

    /**
     * Install the fonts configured in the bundle configuration.
     * @return void
     */
    public function installConfigured(): void
    {
        $dompdf = $this->dompdfFactory->create();

        foreach ($this->fonts as $family_name => $font_family) {
            $this->fontLoader->installFontFamily($dompdf, $family_name, $font_family['normal'], $font_family['bold'] ?? null, $font_family['italic'] ?? null, $font_family['bold_italic'] ?? null);
        }

        if ($this->autodiscoveryPaths !== []) {
            $this->fontLoader->autodiscoverAndInstallFonts(
                $dompdf,
                $this->autodiscoveryPaths,
                $this->autodiscoveryExcludePatterns,
                $this->autodiscoveryFilePattern
            );
        }
    }
}