<?php
/**
 * WHMCS SDK Sample Addon Module Hooks File
 *
 * Hooks allow you to tie into events that occur within the WHMCS application.
 *
 * This allows you to execute your own code in addition to, or sometimes even
 * instead of that which WHMCS executes by default.
 *
 * @see https://developers.whmcs.com/hooks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Register a hook with WHMCS.
 *
 * This sample demonstrates triggering a service call when a change is made to
 * a client profile within WHMCS.
 *
 * For more information, please refer to https://developers.whmcs.com/hooks/
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */

function hook_facturacom_AdminAreaHeadOutput($vars)
{
    $head = '<link href="//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />';

    return $head;
}
add_hook("AdminAreaHeadOutput", 1, "hook_facturacom_AdminAreaHeadOutput");

function hook_facturacom_AdminAreaFooterOutput($vars)
{

    $foot = '<script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js" type="text/javascript"></script>';
    $foot .= '<script src="../modules/addons/facturacom/templates/js/functions.min.js" type="text/javascript"></script>';

    return $foot;
}

if ($_GET['module'] == 'facturacom') {
    add_hook("AdminAreaFooterOutput", 1, "hook_facturacom_AdminAreaFooterOutput");
}
