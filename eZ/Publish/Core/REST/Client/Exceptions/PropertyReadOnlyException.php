<?php
/**
 * File containing the PropertyReadOnlyException class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException as APIPropertyReadOnlyException;

/**
 * This Exception is thrown on a write attempt in a read only property in a value object.
 *
 * @package eZ\Publish\API\Repository\Exceptions
 */
class PropertyReadOnlyException extends APIPropertyReadOnlyException
{
    public function __construct( $propertyName )
    {
        parent::__construct(
            sprintf( 'Property "%s" is read-only.', $propertyName )
        );
    }
}
