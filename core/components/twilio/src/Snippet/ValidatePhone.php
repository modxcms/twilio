<?php

namespace MODX\Twilio\Snippet;

use Twilio\Rest\Client;

class ValidatePhone extends Snippet
{
    public function process()
    {
        $this->modx->lexicon->load('twilio:verify');
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');

        if (empty($sid) || empty($token)) {
            return $this->modx->lexicon('twilio.verify.error.settings');
        }

        if (empty($this->sp['value'])) {
            return $this->modx->lexicon('twilio.verify.error.phone');
        }

        try {
            $twilio = new Client($sid, $token);
            $twilio->lookups->v1->phoneNumbers($this->sp['value'])->fetch();
            return true;
        } catch (\Exception $e) {
            return $this->modx->lexicon('twilio.verify.error.phone_invalid');
        }
    }

}