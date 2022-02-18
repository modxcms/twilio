<?php

namespace MODX\Twilio\Snippet;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseException;

class GetPhone extends Snippet
{
    public function process()
    {
        if (empty($_REQUEST['lp']) || empty($_REQUEST['lu'])) {
            $this->sendError();
            return;
        }

        $username = $this->base64urlDecode($_REQUEST['lu']);
        $password = $this->base64urlDecode($_REQUEST['lp']);

        /** @var \modUser $user */
        $user = $this->modx->getObject('modUser', ['username' => $username]);
        if (!$user) {
            $this->sendError();
            return;
        }

        if ($user->get('active')) {
            $activePage = $this->getOption('activePage', 0, true);
            $this->sendError($activePage);
            return;
        }

        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->addRegister('twilio', 'registry.modFileRegister');

        /** @var \modRegister $reg */
        $reg = $this->modx->registry->twilio;
        $reg->connect();
        $reg->subscribe('/activation/' . $user->get('username'));
        $messages = $reg->read(['remove_read' => false]);

        if (empty($messages)) {
            $this->sendError();
            return;
        }

        $found = false;
        foreach ($messages as $msg) {
            if ($msg === $password) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->sendError();
            return;
        }

        /** @var \modUserProfile $profile */
        $profile = $user->getOne('Profile');
        if (!$profile) {
            $this->sendError();
            return;
        }
        $extended = $profile->get('extended');

        $regionField = $this->getOption('regionField', 'regioncode', true);
        $regionFieldExtended = $this->getOption('phoneFieldExtended', true);
        if ($regionFieldExtended) {
            if (!empty($extended[$regionField])) {
                $region = '+' . $this->format($extended[$regionField]);
            }
        } else {
            if (!empty($profile->get($regionField))) {
                $region = '+' . $this->format($profile->get($regionField));
            }
        }

        $phoneField = $this->getOption('phoneField', 'phone', true);
        $phoneFieldExtended = $this->getOption('phoneFieldExtended', false);
        if ($phoneFieldExtended) {
            $phone = $this->format($extended[$phoneField]);
        } else {
            $phone = $this->format($profile->get($phoneField));
        }
        if (empty($region) && empty($phone)) {
            $this->sendError();
            return;
        }
        try {
            $number = PhoneNumber::parse($region . $phone);
            $this->modx->setPlaceholder('twilio.phone', $number->format(PhoneNumberFormat::E164));
        } catch (PhoneNumberParseException $e) {
            $this->sendError();
            return;
        }
    }

    public function sendError($id = null)
    {
        $errorPage = empty($id) ? $this->getOption('errorPage', 0, true) : $id;
        if (!empty($errorPage)) {
            $url = $this->modx->makeUrl($errorPage, '', '', 'full');
            $this->modx->sendRedirect($url);
        } else {
            $this->modx->sendErrorPage();
        }
    }

    private function format($number): int
    {
        return preg_replace('/\D+/', '', $number);
    }
}
