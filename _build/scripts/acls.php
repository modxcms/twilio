<?php

use xPDO\Transport\xPDOTransport;

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modX $modx */
            $modx =& $object->xpdo;

            $permissions = array('twilio_manage_auth');

            foreach ($permissions as $permission) {
                $accessPermission = $modx->getObject('modAccessPermission', array(
                    'template' => 1,
                    'name' => $permission
                ));

                if (!$accessPermission) {
                    $accessPermission = $modx->newObject('modAccessPermission');
                    $accessPermission->set('template', 1);
                    $accessPermission->set('name', $permission);
                    $accessPermission->set('value', 1);
                    $accessPermission->save();
                }
            }

            break;
    }
}
return true;
