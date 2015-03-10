<?php

namespace Gdbots\Pbj\Extension;

use Gdbots\Common\Microtime;
use Gdbots\Identifiers\TimeUuidIdentifier;
use Gdbots\Pbj\AbstractMessage;
use Gdbots\Pbj\HasMessageRef;
use Gdbots\Pbj\HasMessageRefTrait;

// todo: attempts/retries transient fields?  or transient fields bag?
abstract class AbstractCommand extends AbstractMessage implements Command, HasCorrelator, HasMessageRef
{
    use HasMessageRefTrait;
    use HasCorrerlatorTrait;

    /**
     * {@inheritdoc}
     */
    final public function getMessageId()
    {
        return $this->getCommandId();
    }

    /**
     * {@inheritdoc}
     */
    final public function hasCommandId()
    {
        return $this->has(CommandSchema::COMMAND_ID_FIELD_NAME);
    }

    /**
     * {@inheritdoc}
     */
    final public function getCommandId()
    {
        return $this->get(CommandSchema::COMMAND_ID_FIELD_NAME);
    }

    /**
     * {@inheritdoc}
     * @return static
     */
    final public function setCommandId(TimeUuidIdentifier $id)
    {
        return $this->setSingleValue(CommandSchema::COMMAND_ID_FIELD_NAME, $id);
    }

    /**
     * {@inheritdoc}
     */
    final public function hasMicrotime()
    {
        return $this->has(CommandSchema::MICROTIME_FIELD_NAME);
    }

    /**
     * {@inheritdoc}
     */
    final public function getMicrotime()
    {
        return $this->get(CommandSchema::MICROTIME_FIELD_NAME);
    }

    /**
     * {@inheritdoc}
     * @return static
     */
    final public function setMicrotime(Microtime $microtime)
    {
        return $this->setSingleValue(CommandSchema::MICROTIME_FIELD_NAME, $microtime);
    }
}
