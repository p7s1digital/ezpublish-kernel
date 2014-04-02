<?php
/**
 * File containing the Parser Exception class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Exceptions;

use InvalidArgumentException as PHPInvalidArgumentException;

/**
 * Exception thrown if a parser discovers an error.
 */
class Parser extends PHPInvalidArgumentException
{
}
