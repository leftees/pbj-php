<?php

namespace Gdbots\Pbj;

interface HasCorrelator
{
    /**
     * @return bool
     */
    public function hasCorrelator();

    /**
     * @return MessageRef
     */
    public function getCorrelator();

    /**
     * @param MessageRef $correlator
     * @return static
     */
    public function setCorrelator(MessageRef $correlator);
}
