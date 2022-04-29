<?php

/*
 * WHMCS Factura.com Addon
 * Factura Punto Com SAPI de CV - http://www.factura.com
 *
 * Developed by Farancisco González | Twitter @fgonzalez_n
 *
 * Copyrights (c) 2017 - Factura.com
 */
// FOR DEBUG
//error_reporting(1);
// error_reporting(E_ALL & ~E_NOTICE];
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Module\Addon\Facturacom\Admin\AdminDispatcher;
use WHMCS\Module\Addon\Facturacom\Client\ClientDispatcher;

/**
 * Addon's configuration function
 *
 * @return Array
 */
function facturacom_config()
{
    $configarray = [
        'name' => 'Factura.com',
        'version' => '2.1.0',
        'author' => 'Factura.com',
        'logo' => '../modules/addons/facturacom/factura-addon-icon.png',
        'description' => 'Módulo de integración con el servicio de
                    Factura.com para administrar y emitir facturas electrónicas. Ahora el plugin tiene soporte para timbrado de CFDI 4.0',
        'fields' => [
            'ApiKey' => [
                "FriendlyName" => "API KEY",
                "Type" => "text",
                "Size" => "100",
            ],
            'ApiSecret' => [
                "FriendlyName" => "API SECRET",
                "Type" => "text",
                "Size" => "100",
            ],
            'Serie' => [
                "FriendlyName" => "SERIE FACTURACIÓN",
                "Type" => "text",
                "Size" => "100",
            ],
            'DayOff' => [
                "FriendlyName" => "DÍAS DE TOLERANCIA PARA FACTURAR DESPUÉS DE MES DE COMPRA",
                "Type" => "dropdown",
                "Options" => "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30",
            ],
            'ActivateDate' => [
                "FriendlyName" => "FECHA A PARTIR DE LA CUAL SE PUEDE FACTURAR (dd/mm/yyyy)",
                "Type" => "text",
                "Size" => "100",
            ],
            'IVA' => [
                'FriendlyName' => 'Aplicación de IVA',
                'Type' => 'yesno',
                "Size" => "25",
                "Description" => "Los precios de los productos y servicios ya incluyen IVA",
            ],
            'sandbox' => [
                'FriendlyName' => 'Modo Sandbox',
                'Type' => 'yesno',
                "Size" => "25",
                "Description" => "Si estás probando el módulo en sandbox habilita esta casilla.",
            ],
            'UsoCFDI' => [
                'FriendlyName' => 'Uso de CFDI',
                'Type' => 'text',
                "Size" => "100",
                "Description" => "<br>Debes indicar el UsoCFDI, estipulado en el catalogo <b>c_UsoCFDI</b>, éste se incorporará en el documento",
            ],
            'SendEmail' => [
                'FriendlyName' => 'Enviar por email',
                'Type' => 'yesno',
                "Size" => "25",
                "Description" => "Si deseas que tu cliente reciba su factura en cuanto el mismo se facture desde el área de cliente",
            ],
            'ClaveProdServ' => [
                'FriendlyName' => 'Clave producto/servicio',
                'Type' => 'text',
                "Size" => "60",
                "Description" => "<br>Agrega el valor de <b>ClaveProdServ</b> para tus productos que no tengan datos del sat configurados cómo custom invoices",
            ],
            'ClaveUnidad' => [
                'FriendlyName' => 'Clave Unidad',
                'Type' => 'text',
                "Size" => "60",
                "Description" => "<br>Agrega el valor de <b>c_ClaveUnidad</b> para tus productos que no tengan datos del sat configurados cómo custom invoices",
            ],
            'Unidad' => [
                'FriendlyName' => 'Unidad',
                'Type' => 'text',
                "Size" => "60",
                "Description" => "<br>Agrega el valor de <b>c_ClaveUnidad</b> para tus productos que no tengan datos del sat configurados cómo custom invoices",
            ],
        ],
    ];
    return $configarray;
}
/**
 * Handle addon activation
 *
 * @return Array
 */
function facturacion_activate()
{
    return ['status' => 'success', 'description' => 'Módulo activoado correctamente'];
}
/**
 * Handle addon deactivation
 *
 * @return Array
 */
function facturacion_deactivate()
{
    return ['status' => 'success', 'description' => 'Módulo deshabilitado correctamente'];
}
/**
 * Handle addon upgrade
 *
 * @param Array $vars
 */
function facturacion_upgrade($vars)
{
}
/**
 * Handle addon admin area sidebar
 */
function facturacom_sidebar()
{
}
/**
 * Handle addon admin area output
 *
 * @param Array $vars
 * @return Array
 */
function facturacom_output($vars)
{

        // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables
    // Get module configuration parameters

    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    //echo $response;
}
