<?php

//namespace WHMCS\Module\Addon\Facturacom;
use WHMCS\Module\Addon\Facturacom\Admin\CoreModule;

require_once '../../../../init.php';

/**
 * Handler for Json Ajax Calls from CUSTOMER AREA
 * @author Paul Soberanes  <@soberanees>
 * @copyright (c) Octuber 2015, Factura.com
 */
header('Access-Control-Allow-Methods:GET');
header('Access-Control-Allow-Origin: https://factura.com');
#header('Access-Control-Allow-Credentials : true');

$CoreModule = new CoreModule;

//print_r($_POST); die;
$CoreModule->getCFDI($_GET);
