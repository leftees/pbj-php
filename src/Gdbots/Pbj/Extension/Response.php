<?php

namespace Gdbots\Pbj\Extension;

use Gdbots\Common\Microtime;
use Gdbots\Identifiers\UuidIdentifier;
use Gdbots\Pbj\Message;

interface Response extends Message
{
    /**
     * @return bool
     */
    public function hasResponseId();

    /**
     * @return UuidIdentifier
     */
    public function getResponseId();

    /**
     * @param UuidIdentifier $id
     * @return static
     */
    public function setResponseId(UuidIdentifier $id);

    /**
     * @return bool
     */
    public function hasMicrotime();

    /**
     * @return Microtime
     */
    public function getMicrotime();

    /**
     * @param Microtime $microtime
     * @return static
     */
    public function setMicrotime(Microtime $microtime);

    /**
     * @return bool
     */
    public function hasRequestId();

    /**
     * @return UuidIdentifier
     */
    public function getRequestId();

    /**
     * @param UuidIdentifier $id
     * @return static
     */
    public function setRequestId(UuidIdentifier $id);
}