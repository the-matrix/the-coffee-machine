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

class OrderTransition extends Enum
{
    const NEW_ORDER = 'new-order';
    const WITH_MILK = 'with-milk';
    const MILK_MADE = 'milk-made';
    const NO_MILK = 'no-milk';
    const ADD_MILK = 'add-milk';
    const SERVE_COFFEE = 'serve-coffee';
    const SERVE_COFFEE_MILK = 'serve-coffee-milk';
}