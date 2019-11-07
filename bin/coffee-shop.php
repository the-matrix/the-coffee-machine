<?php
/**
 * <app name>
 *
 * @author    chipp
 * @license   Proprietary. See 'Zinc Software License.pdf'
 * @copyright Zinc Digital Business Solutions Ltd, UK, 2019
 */

include_once dirname(__DIR__) . '/vendor/autoload.php';

use Chippyash\Coffee\CoffeeOrder;
use Chippyash\Coffee\CoffeeType;
use Chippyash\Coffee\OrderState;
use Chippyash\Coffee\OrderTransition;
use Chippyash\StateMachine\Events\EventableStateGraph;
use Chippyash\StateMachine\Events\StateGraphEvent;
use Chippyash\StateMachine\Events\StateGraphEventType;
use Chippyash\StateMachine\Exceptions\InvalidStateException;
use Chippyash\StateMachine\Interfaces\StateGraphEventable;
use Chippyash\StateMachine\State;
use Chippyash\StateMachine\Transition;
use Crell\Tukio\Dispatcher;
use Crell\Tukio\OrderedListenerProvider;

/**
 * Create coffee shop state graph. This could be done by loading definition from file
 */
$coffeeMaker = new EventableStateGraph('The Coffee Shop', 'An Event Driven State Graph Coffee Shop');
$stateNeworder = new State(OrderState::NEW_ORDER);
$stateMakeCoffee = new State(OrderState::MAKE_COFFEE);
$stateMakeMilk = new State(OrderState::MAKE_MILK);
$statePourCoffee = new State(OrderState::POUR_COFFEE);
$statePourMilk = new State(OrderState::POUR_MILK);
$stateServeCoffee = new State(OrderState::SERVE_COFFEE);
$coffeeMaker
    ->addState($stateNeworder)
    ->addState($stateMakeCoffee)
    ->addState($stateMakeMilk)
    ->addState($statePourCoffee)
    ->addState($statePourMilk)
    ->addState($stateServeCoffee)
    ->addTransition($stateNeworder, $stateMakeCoffee, new Transition(OrderTransition::NEW_ORDER))
    ->addTransition($stateMakeCoffee, $stateMakeMilk, new Transition(OrderTransition::WITH_MILK))
    ->addTransition($stateMakeCoffee, $statePourCoffee, new Transition(OrderTransition::NO_MILK))
    ->addTransition($stateMakeMilk, $statePourCoffee, new Transition(OrderTransition::MILK_MADE))
    ->addTransition($statePourCoffee, $statePourMilk, new Transition(OrderTransition::ADD_MILK))
    ->addTransition($statePourMilk, $stateServeCoffee, new Transition(OrderTransition::SERVE_COFFEE_MILK))
    ->addTransition($statePourCoffee, $stateServeCoffee, new Transition(OrderTransition::SERVE_COFFEE));

/**
 * Event management
 */
$listenerProvider = new OrderedListenerProvider();
$dispatcher = new Dispatcher($listenerProvider);
//set dispatcher on graph
$coffeeMaker->setEventDispatcher($dispatcher);
//The main event handler for the StateGraph $coffeeMaker which will change its state
$listenerProvider->addListener([$coffeeMaker, 'eventListener']);

/**
 * Precondition listeners
 */

//set initial state if there isn't one
$listenerProvider->addListener(function(StateGraphEventable $event) use ($coffeeMaker) {
    if (!$event->getEventType()->equals(StateGraphEventType::START_TRANSITION())) {
        return;
    }
    if ($event->getStateGraphObject()->hasState()) {
        return;
    }

    //we assume there is only ONE initial state
    $event->getStateGraphObject()->setState(
        $coffeeMaker->getInitialStates()->reduce(function($carry, State $state) {return $state;})
    );

    echo "{$coffeeMaker->getName()} order is for : {$event->getStateGraphObject()->getCoffeeType()->getValue()}\n\n";
});

//write out the coffeeMaker initial state (before transition)
$listenerProvider->addListener(function(StateGraphEventable $event) {
    if (!$event->getEventType()->equals(StateGraphEventType::START_TRANSITION())) {
        return;
    }
    $state = $event->getStateGraphObject()->getState()->getName();
    echo "Coffee maker is: {$state}\n";
});

/**
 * Post transition listeners
 */

//write out the coffeeMaker new state (after transition)
$listenerProvider->addListener(function(StateGraphEventable $event) {
    if (!$event->getEventType()->equals(StateGraphEventType::END_TRANSITION())) {
        return;
    }
    $state = $event->getStateGraphObject()->getState()->getName();
    echo "Coffee maker changed to: {$state}\n";
});

//Do we need milk for the coffee? Basic decision making
//NB - each of these cases could be written as a separate listener
$listenerProvider->addListener(function(StateGraphEventable $event) use ($coffeeMaker, $dispatcher) {
    if (!$event->getEventType()->equals(StateGraphEventType::END_TRANSITION())) {
        return;
    }

    $object = $event->getStateGraphObject();
    assert($object instanceof CoffeeOrder);
    $state = $object->getState()->getName();
    $hasMilk = $object->hasMilk();
    $transitions = $coffeeMaker->getTransitionsForState($object);
    $transition = null;

    switch ($state) {
        case OrderState::MAKE_COFFEE:
            $transition = $hasMilk ? $transitions[OrderTransition::WITH_MILK] : $transitions[OrderTransition::NO_MILK];
            break;
        case OrderState::POUR_COFFEE:
            $transition = $hasMilk ? $transitions[OrderTransition::ADD_MILK] : $transitions[OrderTransition::SERVE_COFFEE];
            break;
        case OrderState::MAKE_MILK:
            $transition = $transitions[OrderTransition::MILK_MADE];
            break;
        case OrderState::POUR_MILK:
            $transition = $transitions[OrderTransition::SERVE_COFFEE_MILK];
            break;
    }
    if (!is_null($transition)) {
        $event->setPropagationStopped(true);
        $dispatcher->dispatch(new StateGraphEvent(
            $transition,
            $object
        ));
    }
});

//and a final message
$listenerProvider->addListener(function(StateGraphEventable $event) {
    if (!$event->getEventType()->equals(StateGraphEventType::END_TRANSITION())) {
        return;
    }
    if ($event->getStateGraphObject()->getState()->getName() == OrderState::SERVE_COFFEE) {
        echo "\nYour coffee is served\n";
    }
});

//new coffee order
$order = new CoffeeOrder($dispatcher);

/**
 * try it with:
 * CoffeeType::WHITE()
 * CoffeeType::BLACK(
 * CoffeeType::LATTE()
 * CoffeeType::ESPRESSO()
 */
$order->buyCoffee(CoffeeType::LATTE());