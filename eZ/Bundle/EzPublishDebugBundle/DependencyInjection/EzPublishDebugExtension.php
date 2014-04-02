<?php
/**
 * File containing the EzPublishDebugExtension class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDebugBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class EzPublishDebugExtension extends Extension implements PrependExtensionInterface
{
    public function load( array $configs, ContainerBuilder $container )
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../Resources/config' )
        );

        // Base services and services overrides
        $loader->load( 'services.yml' );
    }

    /**
     * Sets the twig base template class to this bundle's in order to collect template infos
     */
    public function prepend( ContainerBuilder $container )
    {
        if ( $container->getParameter( 'kernel.debug' ) )
        {
            $container->prependExtensionConfig(
                'twig',
                array( 'base_template_class' => 'eZ\Bundle\EzPublishDebugBundle\Twig\DebugTemplate' )
            );
        }
    }
}
