<?php

namespace Jbtronics\DompdfFontLoaderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DompdfFontLoaderExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('jbtronics_dompdf_font_loader.auto_install', $config['auto_install']);

        $container->setParameter('jbtronics_dompdf_font_loader.fonts', $config['fonts']);
        $container->setParameter('jbtronics_dompdf_font_loader.autodiscovery.paths', $config['autodiscovery']['paths']);
        $container->setParameter('jbtronics_dompdf_font_loader.autodiscovery.exclude_patterns', $config['autodiscovery']['exclude_patterns']);
        $container->setParameter('jbtronics_dompdf_font_loader.autodiscovery.file_pattern', $config['autodiscovery']['file_pattern']);
    }
}