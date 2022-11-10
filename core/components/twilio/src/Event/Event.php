<?php

namespace MODX\Twilio\Event;

use MODX\Twilio\Twilio;

abstract class Event
{
    /** @var \modX */
    protected $modx;

    /** @var Twilio */
    protected $twilio;

    /** @var array */
    protected $sp = [];

    public function __construct(Twilio &$twilio, array $scriptProperties)
    {
        $this->twilio =& $twilio;
        $this->modx =& $this->twilio->modx;
        $this->sp = $scriptProperties;
    }

    abstract public function run();

    protected function getOption($key, $default = null, $skipEmpty = false)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }
}
