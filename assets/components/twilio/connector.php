<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('twilio.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/twilio/');
$twilio = $modx->getService(
    'twilio',
    'Twilio',
    $corePath . 'model/twilio/',
    array(
        'core_path' => $corePath
    )
);

$action = $_REQUEST['action'] ?? null;
// replace namespace action with processor e.g. MODXBuddy\Processors\ElementCategories\GetList => mgr/element_categories/getlist
if ($action) {
    $action = str_replace('\\', '/', strtolower(str_replace('MODX\\Twilio\\Processors\\', '', $action)));
    $action = preg_replace('/([a-z])([A-Z])/', '$1_$2', $action);
    $action = preg_replace('/([A-Z])([A-Z])([a-z])/', '$1_$2$3', $action);
    $actionArray = explode('/', $action);
    $last = array_pop($actionArray);
    $actionArray[] = str_replace('_', '', $last);
    $action = implode('/', $actionArray);
    $action = $action;
}

/* handle request */
$modx->request->handleRequest(
    array(
        'processors_path' => $twilio->getOption('processorsPath', null, $corePath . 'processors/'),
        'location' => '',
        'action' => $action
    )
);
