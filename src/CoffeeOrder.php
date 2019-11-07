<?php
/**
 * <app name>
 *
 * @author    chipp
 * @license   Proprietary. See 'Zinc Software License.pdf'
 * @copyright Zinc Digital Business Solutions Ltd, UK, 2019
 */
namespace Chippyash\Coffee;

use Chippyash\StateMachine\Events\StateGraphEvent;
use Chippyash\StateMachine\Interfaces\StateAware;
use Chippyash\StateMachine\Traits\HasState;
use Chippyash\StateMachine\Transition;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class CoffeeOrder
 *
 * CoffeeOrder is a StateAware object that can be transitioned by a StateGraph
 *
 * @todo move StateAware functionality to trait in finite-state package
 */
class CoffeeOrder implements StateAware
{
    use HasState;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var CoffeeType
     */
    protected $coffeeType;
    /**
     * @var bool
     */
    protected $hasMilk;

    /**
     * CoffeeOrder constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function buyCoffee(CoffeeType $typeOfCoffee)
    {
        //set some variables that the various event listeners can utilise
        $this->coffeeType = $typeOfCoffee;
        $this->hasMilk = in_array(
            $typeOfCoffee->getValue(),
            [CoffeeType::LATTE, CoffeeType::WHITE]
        );

        //initiate the new order event
        $this->eventDispatcher->dispatch(
            new StateGraphEvent(
                new Transition(OrderTransition::NEW_ORDER),
                $this
            )
        );
    }

    /**
     * @return CoffeeType
     */
    public function getCoffeeType(): CoffeeType
    {
        return $this->coffeeType;
    }

    /**
     * @return bool
     */
    public function hasMilk(): bool
    {
        return $this->hasMilk;
    }
}