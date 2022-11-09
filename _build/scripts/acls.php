<?php

use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modX;
use xPDO\Transport\xPDOTransport;

/**
 * @var xPDOTransport $transport
 * @var array $object
 * @var array $options
 */

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) {
    return true;
}

/** @var modX $modx */
$modx =& $transport->xpdo;

$permissions = ['twilio_manage_auth'];

foreach ($permissions as $permission) {
    $accessPermission = $modx->getObject(modAccessPermission::class, [
        'template' => 1,
        'name' => $permission,
    ]);

    if (!$accessPermission) {
        $accessPermission = $modx->newObject(modAccessPermission::class);
        $accessPermission->set('template', 1);
        $accessPermission->set('name', $permission);
        $accessPermission->set('value', 1);
        $accessPermission->save();
    }
}
