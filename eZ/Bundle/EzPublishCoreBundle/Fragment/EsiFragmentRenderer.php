<?php
/**
 * File containing the EsiFragmentRenderer class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer as BaseRenderer;

class EsiFragmentRenderer extends BaseRenderer
{
    /**
     * @var FragmentUriGenerator
     */
    private $fragmentUriGenerator;

    protected function generateFragmentUri( ControllerReference $reference, Request $request, $absolute = false )
    {
        if ( !isset( $this->fragmentUriGenerator ) )
        {
            $this->fragmentUriGenerator = new FragmentUriGenerator;
        }

        $this->fragmentUriGenerator->generateFragmentUri( $reference, $request, $absolute );
        return parent::generateFragmentUri( $reference, $request, $absolute );
    }
}
