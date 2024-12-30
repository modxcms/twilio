<?php

namespace MODX\Twilio\Processors\Users;

use MODX\Revolution\modUser;
use MODX\Revolution\modUserGroup;
use MODX\Revolution\modUserGroupMember;
use MODX\Revolution\modUserGroupRole;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\modUserSetting;
use MODX\Revolution\Processors\Model\GetListProcessor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = modUser::class;
    public $languageTopics = array('user', 'twilio:default');
    public $defaultSortField = 'username';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'user';
    public $permission = 'twilio_manage_auth';

    public function beforeQuery()
    {
        if ($this->getProperty('export')) {
            $this->setProperty('start', 0);
            $this->setProperty('limit', 0);
        }

        return parent::beforeQuery();
    }

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
        $objectArray['add_groups'] = [];
        $groups = $object->getUserGroups();
        foreach ($groups as $g) {
            $role = $this->modx->getObject(modUserGroupMember::class, [
                'user_group' => $g,
                'member' => $object->get('id'),
            ]);
            if ($g == $objectArray['primary_group']) {
                $objectArray['primary_group_role'] = $this->modx->getObject(
                    modUserGroupRole::class,
                    $role->get('role')
                )->get('name');
                continue;
            }
            $objectArray['add_groups'][] = [
                'id' => $g,
                'name' => $this->modx->getObject(modUserGroup::class, $g)->get('name'),
                'role' => $this->modx->getObject(modUserGroupRole::class, $role->get('role'))->get('name'),
            ];
        }
        $objectArray['profile_lastlogin'] = $objectArray['profile_lastlogin'] ?
            date('Y-m-d H:i:s', $objectArray['profile_lastlogin']) : null;

        unset($objectArray['profile_extended']);
        return $objectArray;
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->leftJoin(modUserProfile::class, 'Profile');
        $c->leftJoin(
            modUserSetting::class,
            'UserSettings',
            'UserSettings.key = "twilio.totp" AND UserSettings.user = modUser.id'
        );
        $c->leftJoin(
            modUserGroup::class,
            'PrimaryGroup',
        );
        $search = $this->getProperty('search');
        if (!empty($search)) {
            $c->where(array(
                'modUser.username:LIKE' => "%{$search}%",
                'OR:Profile.fullname:LIKE' => "%{$search}%",
                'OR:Profile.email:LIKE' => "%{$search}%",
            ));
        }
        $active = $this->getProperty('active');
        if (in_array($active, ['0', '1'])) {
            $c->where(array(
                'modUser.active' => $active,
            ));
        }
        $status = $this->getProperty('2fa');
        if ($status == '1') {
            $c->where(array(
                'UserSettings.value' => 1,
            ));
        }
        if ($status == '0') {
            $c->where(array(
                'UserSettings.value' => 0,
                'OR:UserSettings.value:=' => null,
            ));
        }
        return parent::prepareQueryBeforeCount($c);
    }

    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $c->select($this->modx->getSelectColumns(
            modUser::class,
            'modUser',
            '',
            ['id', 'username']
        ));
        $c->select($this->modx->getSelectColumns(
            modUserProfile::class,
            'Profile',
            'profile_',
            ['fullname', 'email', 'blocked', 'lastlogin', 'comment', 'extended']
        ));
        $c->select($this->modx->getSelectColumns(
            modUserSetting::class,
            'UserSettings',
            'totp_',
            ['value']
        ));
        $c->select($this->modx->getSelectColumns(
            modUserGroup::class,
            'PrimaryGroup',
            'primary_group_',
            ['name']
        ));
        $c->prepare();
        return parent::prepareQueryAfterCount($c);
    }

    public function outputArray(array $array, $count = false)
    {
        if ($this->getProperty('export')) {
            $header = [
                'id',
                'username',
                'active',
                'profile_blocked',
                'profile_fullname',
                'profile_email',
                'profile_lastlogin',
                'profile_comment',
                'totp_value',
                'totp_status',
                'primary_group_name',
                'primary_group_role',
                'add_groups',
            ];
            $filename = 'totp-users.' . time() . '.csv';
            $fp = fopen($filename, 'w');
            fputcsv($fp, $header);
            foreach ($array as $arr) {
                foreach ($arr as $k => $v) {
                    if (is_array($v)) {
                        $arr[$k] = json_encode($v);
                    }
                }
                $data = [];
                foreach ($header as $h) {
                    $data[] = $arr[$h];
                }
                fputcsv($fp, array_values($data));
            }
            fclose($fp);
            header('Content-type: text/csv');
            header('Content-disposition:attachment; filename="' . $filename . '"');
            readfile($filename);
            return '';
        }

        return parent::outputArray($array, $count);
    }
}
