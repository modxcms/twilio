<?php

$_lang['twilio.email.subject'] = '2-step verification login has been activated';
$_lang['twilio.email.body'] = '<html><body><p>Hello [[+username]],</p>'
    . '<p>You are receiving this email because 2-step verification is enabled for your account. '
    . 'To use 2-step verification you will need to download Authy, Google Authenticator, 1Password, or a '
    . 'similar application on your computer or mobile device. For some devices you might need a '
    . 'QR-code scanner as well. Scan the below QR-code or enter the <b>[[+secret]]</b> into your '
    . 'application, upon the success of this process you will see an authentication key changing '
    . 'every 30 seconds. This is the key required during your login.</p>'
    . '<img src="[[+qr]]" alt="QR Code" /></body></html>';
