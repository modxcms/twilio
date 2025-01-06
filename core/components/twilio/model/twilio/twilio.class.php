<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use MODX\Twilio\Twilio as TwilioBase;

class Twilio extends TwilioBase
{
    public function __construct(\modX &$modx, array $options = [])
    {
        $this->modx =& $modx;
        $assetsUrl = $this->getOption(
            'assets_url',
            $options,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/twilio/'
        );
        $options['connectorUrl'] = $assetsUrl . 'connector.php';
        parent::__construct($modx, $options);
    }
}