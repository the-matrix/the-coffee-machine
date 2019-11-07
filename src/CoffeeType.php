<?php
/**
 * <app name>
 *
 * @author    chipp
 * @license   Proprietary. See 'Zinc Software License.pdf'
 * @copyright Zinc Digital Business Solutions Ltd, UK, 2019
 */
namespace Chippyash\Coffee;

use MyCLabs\Enum\Enum;

/**
 * Class CoffeeType
 *
 * @method static CoffeeType LATTE()
 * @method static CoffeeType ESPRESSO()
 * @method static CoffeeType BLACK()
 * @method static CoffeeType WHITE()
 */
class CoffeeType extends Enum
{
    const LATTE = 'latte';
    const ESPRESSO = 'espresso';
    const BLACK = 'black';
    const WHITE = 'white';
}