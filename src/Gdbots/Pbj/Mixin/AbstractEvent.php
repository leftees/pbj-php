<?php

namespace Gdbots\Pbj\Mixin;

use Gdbots\Common\Microtime;
use Gdbots\Identifiers\TimeUuidIdentifier;
use Gdbots\Pbj\AbstractMessage;
use Gdbots\Pbj\GeneratesMessageRef;
use Gdbots\Pbj\GeneratesMessageRefTrait;
use Gdbots\Pbj\HasCorrelator;
use Gdbots\Pbj\HasCorrerlatorTrait;

abstract class AbstractEvent extends AbstractMessage implements DomainEvent, GeneratesMessageRef, HasCorrelator
{
    use GeneratesMessageRefTrait;
    use HasCorrerlatorTrait;

    /**
     * {@inheritdoc}
     */
    final public function getMessageId()
    {
        return $this->getEventId();
    }

    /**
     * @return bool
     */
    final public function hasEventId()
    {
        return $this->has(Event::EVENT_ID_FIELD_NAME);
    }

    /**
     * @return TimeUuidIdentifier
     */
    final public function getEventId()
    {
        return $this->get(Event::EVENT_ID_FIELD_NAME);
    }

    /**
     * @param TimeUuidIdentifier $id
     * @return static
     */
    final public function setEventId(TimeUuidIdentifier $id)
    {
        return $this->setSingleValue(Event::EVENT_ID_FIELD_NAME, $id);
    }

    /**
     * @return bool
     */
    final public function hasMicrotime()
    {
        return $this->has(Event::MICROTIME_FIELD_NAME);
    }

    /**
     * @return Microtime
     */
    final public function getMicrotime()
    {
        return $this->get(Event::MICROTIME_FIELD_NAME);
    }

    /**
     * @param Microtime $microtime
     * @return static
     */
    final public function setMicrotime(Microtime $microtime)
    {
        return $this->setSingleValue(Event::MICROTIME_FIELD_NAME, $microtime);
    }
}