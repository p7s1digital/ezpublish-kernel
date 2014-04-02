<?php
/**
 * File containing the LocalePassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocalePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LocalePassTest extends AbstractCompilerPassTest
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new LocalePass() );
    }

    public function testLocaleListener()
    {
        $this->setDefinition( 'locale_listener', new Definition() );
        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'locale_listener',
            'setConfigResolver',
            array( new Reference( 'ezpublish.config.resolver' ) )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'locale_listener',
            'setLocaleConverter',
            array( new Reference( 'ezpublish.locale.converter' ) )
        );
    }
}
