# Font loader for dompdf Bundle

This bundle provides a simple way to load/install fonts of your choice for the [dompdf](https://github.com/dompdf/dompdf) library in a symfony project.
This bundle offers functionality to load font families programmatically or automatically on cache warmup.
You can specify each font family with a name and a path to the font files or you let the bundle autodiscover all fonts in a directory.

## Feautures
* Services for easy installation of font families
* Configure font families in your config files, which will be loaded automatically on cache warmup
* Autodiscover all fonts in configured directories

## Requirements
* Symfony 6
* PHP 8.1 or later

## Installation
1. Install the bundle `composer require jbtronics/dompdf-font-loader-bundle`
2. Enable the bundle in your `config/bundles.php` (normally done by Symfony flex automatically)
3. Put the font TTF files you want to use in a directory the webserver can access (preferably somewhere in your project folder)
4. Add a config file `config/packages/jbtronics_dompdf_font_loader.yaml`, with the content described below (and changed according to your need)

## Configuration

```yaml
dompdf_font_loader:
  
  # Set this to true to enable the automatic font loading on cache warmup, without it you have to load the fonts
  # manually via the ConfiguredFontsInstaller service
  auto_install: true

  # You can specify font families here manually
  fonts:
    my_font: # The name of the font family (used to access it in dompdf later)
      # A font family consists of up to four font files (normal, bold, italic, bold_italic)
      normal: "%kernel.project_dir%/assets/fonts/my_font.ttf"
      bold: "%kernel.project_dir%/assets/fonts/my_font_bold.ttf"
      italic: "%kernel.project_dir%/assets/fonts/my_font_italic.ttf"
      bold_italic: "%kernel.project_dir%/assets/fonts/my_font_bold_italic.ttf"
      
    # But only the normal font file is required, the others can be omitted
    unifont:
      normal: "%kernel.project_dir%/assets/fonts/unifont.ttf"

  # Autodiscover allows you to specify directories, where all fonts will be loaded automatically
  autodiscovery:
    # Each of this directory will be scanned for font files 
    paths:
      - "%kernel.project_dir%/assets/fonts"
      - "%kernel.project_dir%/vendor/fonts/package/ttfs"
    exclude_patterns:
      # You can exclude certain patterns from the autodiscovery if you want
       - "exclude_this_font.ttf"
```

The fonts and autodiscovery keys are both optional, but at least one of them is required to load fonts.

## Usage

When you have enabled the `auto_install` option, you do not have to do anything else, the fonts will be loaded automatically on cache warmup (when `php bin/console cache:clear` is run).
The bundle will copy the font files to the dompdf font directory, create font metrics and register them in the dompdf library.

### Autodiscovery
The autodiscovery mechanism will scan the configured directories for TTF files and register them as font families with the name of the font file. 
It also tries to detect the type of the font based on a suffix: `_bold` or `_b` will be detected as bold fonts, `_italic`, `_i` as italic fonts, and `_bold_italic` or `_bi` as bold italic.
So the `my_font_bold.ttf` will be registered as bold font of the `my_font` family and so on, while `my_font.ttf` will be registered as normal font of the `my_font` family.

In principle dompdf should be able to use OTF files as well, however in my tests it did not work, so autodiscovery only detects TTF files by default. You can change the detected file types via the `autodiscovery.file_pattern` option.

### Specify DOMPDF font location
Dompdf has its own font directory, where it stores the font files and metrics. This is configured on a per instance basis on a dompdf object with the `set_option('fontDir', $path)` method.
To specify the font directory for the dompdf instance used by this bundle, you must decorate the `DompdfFactoryInterface` and configure the object in the `create()` method:

```php
#[AsDecorator(decorates: DompdfFactoryInterface::class)]
class MyDompdfFactory implements DompdfFactoryInterface
{
    public function create(): Dompdf
    {
        return new Dompdf(['fontDir' => '%kernel.project_dir%/var/dompdf/fonts']);
    }
}
```

### Manual font loading/installation
This bundle offers the `DompdfFontLoader` service, which can be used to install font families manually.
You can either install a single font family with the `installFontFamily()` method or install all found fonts in a folder with the `autodiscoverAndInstallFonts()` method.

## License
This bundle is licensed under the MIT license. See [LICENSE](LICENSE) for details.

## Credits
* [dompdf](https://github.com/dompdf/dompdf)
* This bundle was inspired by the offical dompdf util script [load_font.php](https://github.com/dompdf/utils/blob/master/load_font.php)