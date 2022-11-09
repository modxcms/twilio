<?php

namespace MODX\Twilio\Snippet;

use Twilio\Rest\Client;

class ValidatePhone extends Snippet
{
    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');

        if (empty($sid) || empty($token)) {
            return 'Missing Twilio system settings.';
        }

        if (empty($this->sp['value'])) {
            return 'Phone is required';
        }

        try {
            $twilio = new Client($sid, $token);
            $twilio->lookups->v1->phoneNumbers($this->sp['value'])->fetch();
            return true;
        } catch (\Exception $e) {
            return 'Invalid phone number';
        }
    }

}