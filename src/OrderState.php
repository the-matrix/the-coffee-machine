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

class OrderState extends Enum
{
    const NEW_ORDER = 'new-order';
    const MAKE_COFFEE = 'make-coffee';
    const MAKE_MILK = 'make-milk';
    const POUR_COFFEE = 'pour-coffee';
    const POUR_MILK = 'pour-milk';
    const SERVE_COFFEE = 'serve-coffee';
}