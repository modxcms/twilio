<?php

namespace MODX\Twilio\v2\Snippet;

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

        /** @var \modFileRegister $reg */
        $reg = $this->getRegister();
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

        $phoneField = $this->getOption('phoneField', 'phone', true);
        if ($phoneField !== 'phone') {
            $phoneField = 'mobilephone';
        }

        $phone = $profile->get($phoneField);
        $this->modx->setPlaceholder('twilio.phone', $phone);
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
}
