<?php
/**
 * File containing the NotFoundException class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Exceptions\NotFoundException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\NotFoundException
 */
class NotFoundException extends APINotFoundException
{
}
