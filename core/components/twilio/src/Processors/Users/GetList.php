<?php
namespace MODX\Twilio\Processors\Users;

use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = 'modUser';
    public $languageTopics = array('user', 'twilio:default');
    public $defaultSortField = 'username';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'user';
    public $permission = 'twilio_manage_auth';

    public function prepareRow(xPDOObject $object)
    {
        $objectArray = $object->toArray();
        $objectArray['totp_value'] = (int)$object->get('totp_value');
        $status = null;
        $extended = $object->get('profile_extended');
        if (!empty($extended)) {
            $extended = json_decode($extended, true, 512, JSON_THROW_ON_ERROR);
            if (!empty($extended['twilio_totp'])) {
                $status = $extended['twilio_totp']['status'];
            }
        }
        $objectArray['totp_status'] = (!$status && $objectArray['totp_value']) ? 'not_set' : $status;
        unset($objectArray['profile_extended']);
        return $objectArray;
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->leftJoin('modUserProfile', 'Profile');
        $c->leftJoin(
            'modUserSetting',
            'UserSettings',
            'UserSettings.key = "twilio.totp" AND UserSettings.user = modUser.id'
        );
        $search = $this->getProperty('search');
        if (!empty($search)) {
            $c->where(array(
                'modUser.username:LIKE' => "%{$search}%",
                'OR:Profile.fullname:LIKE' => "%{$search}%",
                'OR:Profile.email:LIKE' => "%{$search}%",
            ));
        }
        return parent::prepareQueryBeforeCount($c);
    }

    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns(
            'modUser',
            'modUser',
            '',
            ['id','username']
        ));
        $c->select($this->modx->getSelectColumns(
            'modUserProfile',
            'Profile',
            'profile_',
            ['fullname', 'email', 'extended']
        ));
        $c->select($this->modx->getSelectColumns(
            'modUserSetting',
            'UserSettings',
            'totp_',
            ['value']
        ));
        $c->prepare();
        return parent::prepareQueryAfterCount($c);
    }
}
