<?php

namespace Jbtronics\DompdfFontLoaderBundle\Services;

use Dompdf\Dompdf;
use FontLib\Font;
use Symfony\Component\Finder\Finder;

class DompdfFontLoader
{

    public final const DEFAULT_FILE_PATTERN = '/\.(ttf|otf)$/';


    /**
     * Find all fonts in the given directories and install them in the given Dompdf instance.
     * @param  Dompdf  $dompdf
     * @param  array  $autodiscovery_paths
     * @param  array  $exclude_patterns
     * @param  string  $file_pattern
     * @return void
     */
    public function autodiscoverAndInstallFonts(Dompdf $dompdf, array $autodiscovery_paths, array $exclude_patterns = [], string $file_pattern = self::DEFAULT_FILE_PATTERN): void
    {
        //Find all fonts in the given paths
        foreach ($autodiscovery_paths as $autodiscovery_path) {
            $detected_fonts = $this->findFontFamilies($autodiscovery_path, $exclude_patterns, $file_pattern);
            //And install the found fonts
            foreach ($detected_fonts as $family_name => $family_fonts) {
                $this->installFontFamily($dompdf, $family_name, $family_fonts['normal'], $family_fonts['bold'] ?? null, $family_fonts['italic'] ?? null, $family_fonts['bold_italic'] ?? null);
            }
        }
    }

    /**
     * Search for font files in the given directory and return a grouped array of font families.
     * @param  string  $autodiscovery_path
     * @param  string[]  $exclude_patterns
     * @param  string  $file_pattern
     * @return array An array of the form
     * [
     *  'font1' => [
     *          'normal' => '/path/to/font1.ttf',
     *          'bold' => '/path/to/font1_b.ttf',
     *          ],
     * 'font2' => [
     *          'normal' => '/path/to/font2.ttf',
     *         ],
     * ]
     */
    public function findFontFamilies(string $autodiscovery_path, array $exclude_patterns = [], string $file_pattern = self::DEFAULT_FILE_PATTERN): array
    {
        $finder = new Finder();
        $finder->files()->in($autodiscovery_path)->name($file_pattern);
        if ($exclude_patterns) {
            $finder->notPath($exclude_patterns);
        }

        //Convert the iterator to an array
        $files = iterator_to_array($finder->getIterator());

        //Group the files by their basename (without extension and _suffix)
        $grouped = array_reduce($files, function ($carry, \SplFileInfo $file): array {
            $basename = $file->getBasename('.' . $file->getExtension());
            $basename = preg_replace('/_(b|i).*$/', '', $basename);
            $carry[$basename][] = $file;
            return $carry;
        }, []);

        $result = [];

        //Convert the array to the required format
        foreach ($grouped as $family_name => $family_fonts) {
            $result[$family_name] = [];
            foreach ($family_fonts as $font) {
                /** @var \SplFileInfo $font */
                $type = FontFamilyType::determineType($font->getPathname())->value;
                $result[$family_name][$type] = $font->getPathname();
            }
        }

        return $result;
    }

    /**
     * Load the font (family) for the given Dompdf instance (or another one with the same settings).
     * This code is similar to the mechanism of install_font_family in https://github.com/dompdf/utils/blob/master/load_font.php
     *
     * @param  Dompdf  $dompdf The dompdf object whose settings should be used
     * @param  string  $fontname The under which the font family should be registered
     * @param  string  $normal The path to the normal font file (required, must be a .ttf or .otf file)
     * @param  string|null  $bold   The path to the bold font file (optional, must be a .ttf or .otf file)
     * @param  string|null  $italic   The path to the italic font file (optional, must be a .ttf or .otf file)
     * @param  string|null  $bold_italic The path to the bold italic font file (optional, must be a .ttf or .otf file)
     * @return void
     */
    public function installFontFamily(Dompdf $dompdf, string $fontname, string $normal, string $bold = null, string $italic = null, string $bold_italic = null): void
    {
        //Retrieve the font metrics instance
        $fontMetrics = $dompdf->getFontMetrics();

        //Retrieve folders from dompdf instance
        $fontDir = $dompdf->getOptions()->getFontDir();

        //Check that the given fonts exist and have a valid extension
        $this->checkFontFile($normal);
        if ($bold) {
            $this->checkFontFile($bold);
        }
        if ($italic) {
            $this->checkFontFile($italic);
        }
        if ($bold_italic) {
            $this->checkFontFile($bold_italic);
        }

        //Copy the font files to the font directory of dompdf
        $normal = $this->copyFontFileAndCreateFontMetric($normal, $fontDir);
        if ($bold) {
            $bold = $this->copyFontFileAndCreateFontMetric($bold, $fontDir);
        }
        if ($italic) {
            $italic = $this->copyFontFileAndCreateFontMetric($italic, $fontDir);
        }
        if ($bold_italic) {
            $bold_italic = $this->copyFontFileAndCreateFontMetric($bold_italic, $fontDir);
        }

        //And register the font family. For this we need the font names without the extension
        $fontFamily = [
            'normal' => pathinfo($normal, PATHINFO_FILENAME),
        ];
        if ($bold) {
            $fontFamily['bold'] = pathinfo($bold, PATHINFO_FILENAME);
        }
        if ($italic) {
            $fontFamily['italic'] = pathinfo($italic, PATHINFO_FILENAME);
        }
        if ($bold_italic) {
            $fontFamily['bold_italic'] = pathinfo($bold_italic, PATHINFO_FILENAME);
        }

        //Add as new font family
        $fontMetrics->setFontFamily($fontname, $fontFamily);
        //And save the font family
        $fontMetrics->saveFontFamilies();
    }

    /**
     * Create a font metric file for the given font file.
     * The path
     */
    private function createFontMetric(string $font, string $fontCacheDir): void
    {
        $fontObj = Font::load($font);
        if ($fontObj === null) {
            throw new \RuntimeException('Font file ' . $font . ' could not be loaded');
        }

        //Retrieve basename
        $fontName = pathinfo($font, PATHINFO_FILENAME);


        $fontObj->saveAdobeFontMetrics($fontCacheDir . '/' . $fontName . '.ufm');
        $fontObj->close();
    }

    /**
     * Check if the given font file exists and has a valid extension (.ttf or .otf)
     * @param  string  $font_path
     * @return void
     */
    private function checkFontFile(string $font_path): void
    {
        if (!file_exists($font_path)) {
            throw new \RuntimeException('Font file ' . $font_path . ' does not exist');
        }

        if (!is_readable($font_path)) {
            throw new \RuntimeException('Font file ' . $font_path . ' is not readable');
        }

        $extension = pathinfo($font_path, PATHINFO_EXTENSION);
        if (!in_array($extension, ['ttf', 'otf'])) {
            throw new \RuntimeException('Font file ' . $font_path . ' has an invalid extension. Only .ttf and .otf are supported');
        }
    }

    /**
     * Copy the given font file to the given font directory and create a font metric.
     * @return string The target file name of the copied font file
     */
    private function copyFontFileAndCreateFontMetric(string $font_file, string $fontDir): string
    {
        $target_file = $fontDir . '/' . basename($font_file);

        //Check if the file already exists (check existence and compare md5 hash)
        if (file_exists($target_file) && md5_file($font_file) === md5_file($target_file)) {
            return $target_file;
        }

        if(!copy($font_file, $target_file)) {
            throw new \RuntimeException('Could not copy font file ' . $font_file . ' to ' . $target_file);
        }

        //Create the font metric
        $this->createFontMetric($target_file, $fontDir);

        return $target_file;
    }
}