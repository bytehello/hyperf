<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model\Concerns;

use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Str;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @method retrieved(Model $model)
 * @method creating(Model $model)
 * @method created(Model $model)
 * @method updating(Model $model)
 * @method updated(Model $model)
 * @method saving(Model $model)
 * @method saved(Model $model)
 * @method restoring(Model $model)
 * @method restored(Model $model)
 * @method deleting(Model $model)
 * @method deleted(Model $model)
 * @method forceDeleted(Model $model)
 */
trait HasEvents
{
    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Model events.
     *
     * @var array
     */
    protected $dispatchesEvents = [];

    /**
     * Fire the given event for the model.
     * @return object|StoppableEventInterface|null
     */
    protected function fireModelEvent(string $event): ?object
    {
        $dispatcher = $this->getEventDispatcher();
        if (! $dispatcher instanceof EventDispatcherInterface) {
            return null;
        }

        $result = $this->fireCustomModelEvent($event);
        // If custom event does not exist, the fireCustomModelEvent() method will return null.
        if (! is_null($result)) {
            return $result;
        }

        $eventName = 'Hyperf\\Database\\Model\\Events\\' . Str::studly($event);
        return $dispatcher->dispatch(new $eventName($this, $event));
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @param string $event
     * @param string $method
     * @return null|mixed
     */
    protected function fireCustomModelEvent($event)
    {
        if (! isset($this->dispatchesEvents[$event])) {
            return;
        }

        return $this->getEventDispatcher()->dispatch(new $this->dispatchesEvents[$event]($this));
    }

    /**
     * Filter the model event results.
     */
    protected function filterModelEventResults($result)
    {
        if (is_array($result)) {
            $result = array_filter($result, function ($response) {
                return ! is_null($response);
            });
        }

        return $result;
    }
}
