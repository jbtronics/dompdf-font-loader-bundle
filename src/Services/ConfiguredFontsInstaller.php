<?php

namespace Jbtronics\DompdfFontLoaderBundle\Services;

class ConfiguredFontsInstaller
{
    public function __construct(
        private readonly DompdfFactoryInterface $dompdfFactory,
        private readonly DompdfFontLoader $fontLoader,
        private readonly array $fonts,
        private readonly array $autodiscoveryPaths,
        private readonly array $autodiscoveryExcludePatterns,
        private readonly string $autodiscoveryFilePattern
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