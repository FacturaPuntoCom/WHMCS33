<?php

namespace WHMCS\Module\Addon\Facturacom\Admin;

use GuzzleHttp\Client;
use WHMCS\Database\Capsule;
use Carbon\Carbon;

class CoreModule
{

    public function __construct()
    {

    }

    public function getGonfiguration()
    {
        $setting = false;
        $settings = Capsule::table('tbladdonmodules')->where('module', 'facturacom')->get();

        if (!is_null($settings)) {
            foreach ($settings as $value) {
                # code...
                $setting[$value->setting] = $value->value;
            }
        }

        return $setting;
    }

    public function getURL($parameters)
    {
        if ($parameters['sandbox'] === 'on') {
            return 'https://sandbox.factura.com/api/';
        } else {
            return 'https://factura.com/api/';
        }
    }

    public function getWhmcsInvoicesAll($UserID)
    {
        if (!isset($UserID)) {
            return array(
                'Error' => 'No se ha recibido el id del cliente.',
            );
        }

        $configEntity = $this->getGonfiguration();
        $invoiceList = [];
        $facturaInvoiceList = [];
        $invoicesObj = Capsule::table('tblinvoices')
            ->where('tblinvoices.userid', $UserID)
            ->get();

        foreach ($invoicesObj as $key => $value) {
            $invoiceList[$value->id]["orderId"] = $value->id;
            $invoiceList[$value->id]["orderNum"] = $value->id;
            $invoiceList[$value->id]["clientId"] = $value->userid;
            $invoiceList[$value->id]["orderDate"] = date("d-m-Y", strtotime($value->date));
            $invoiceList[$value->id]["invoiceDueDate"] = date("d-m-Y", strtotime($value->duedate));
            $invoiceList[$value->id]["invoiceDatePaid"] = (!preg_match('/[1-9]/', $value->datepaid)) ? null : date("d-m-Y", strtotime($value->datepaid));
            $invoiceList[$value->id]["total"] = $value->total;
            $invoiceList[$value->id]["status"] = $value->status;
            $invoiceList[$value->id]["orderdata"] = $value->id;
            $invoiceList[$value->id]["sent"] = $configEntity['SendEmail'];
            $invoiceList[$value->id]["open"] = 'true';

            if ($value->status != "Paid") {
                $invoiceList[$value->id]["open"] = 'false';
            }

            // open
            /* validar que la factura esté dentro del mes +X días y a partir
            de la fecha de facturación configurada
             */
            $order_month = date("m", strtotime($value->datepaid));
            $order_year = date("Y", strtotime($value->datepaid));
            $current_day = date("d");
            $current_month = date("m");
            $current_year = date("Y");

            if (is_null($configEntity) && !is_array($configEntity)) {
                $invoiceList[$value->id]["open"] = 'false';
            }

            $arr = explode('/', $configEntity['activateDate']);

            /* formatear la fecha a dd-mm-aaaa porque la fecha datepaid
            tiene ese formato en WHMCS y deben tener el mismo formato para
            compararse. */
            $newDate = $arr[0] . '-' . $arr[1] . '-' . $arr[2];

            $activateDate = strtotime($newDate); //1 septiembre 2015
            $paidDate = strtotime($value->datepaid); //6 Octubre 2015

            // Validate plugin activation date vs payment date
            if ($paidDate < $activateDate) {
                $invoiceList[$value->id]["open"] = 'false';
            }

            //Validate invoice total is not zero
            if ($value->total <= 0) {
                $invoiceList[$value->id]["open"] = 'false';
            }

            //vamos sobre el tiempo de tolerancia para facturar.
            Carbon::setLocale('es');
            $strtotime = strtotime($value->datepaid);
            $fpago = explode("-", date("Y-m-d", $strtotime));

            if(is_null($strtotime) || $strtotime < 1) {
                $dt = Carbon::createFromDate(2000, 01, 01);
            } else {
                $dt = Carbon::createFromDate($fpago[0], $fpago[1], $fpago[2]);
            }

            //Sacamos la diferencia
            if($dt->diffInDays(Carbon::now()) > 30) {
                $diferenciaDicas = $dt->diffInDays(Carbon::now());
            } else {
                $diferenciaDicas =  ($dt->diffInDays(Carbon::now()) - $dt->daysInMonth);
            }

            //si la orden no está facturada y tiene dias entonces
            if(intval($diferenciaDicas) > $configEntity['DayOff']) {
                $invoiceList[$value->id]["open"] = false;
            }

        }

        return $invoiceList;
    }

    public function getWhmcsInvoices($UserID)
    {
        if (!isset($UserID)) {
            return array(
                'Error' => 'No se ha recibido el id del cliente.',
            );
        }

        $configEntity = $this->getGonfiguration();
        $invoiceList = [];
        $facturaInvoiceList = [];
        $invoicesObj = Capsule::table('tblinvoices')
            ->where('tblinvoices.userid', $UserID)
            ->get();
        $NumInvoices = [];

        foreach ($invoicesObj as $key => $value) {

            //asigamos los pedidos a un arreglo
			$NumInvoices[] = $value->id;

            $invoiceList[$value->id]["orderId"] = $value->id;
            $invoiceList[$value->id]["orderNum"] = $value->id;
            $invoiceList[$value->id]["clientId"] = $value->userid;
            $invoiceList[$value->id]["orderDate"] = date("d-m-Y", strtotime($value->date));
            $invoiceList[$value->id]["invoiceDueDate"] = date("d-m-Y", strtotime($value->duedate));
            $invoiceList[$value->id]["invoiceDatePaid"] = (!preg_match('/[1-9]/', $value->datepaid)) ? null : date("d-m-Y", strtotime($value->datepaid));
            $invoiceList[$value->id]["total"] = $value->total;
            $invoiceList[$value->id]["status"] = $value->status;
            $invoiceList[$value->id]["orderdata"] = base64_decode($value->id);
            $invoiceList[$value->id]["sent"] = false;
            $invoiceList[$value->id]["open"] = true;

            if ($value->status != "Paid") {
                $invoiceList[$value->id]["open"] = false;
            }

            // open
            /* validar que la factura esté dentro del mes +X días y a partir
            de la fecha de facturación configurada
             */
            $order_month = date("m", strtotime($value->datepaid));
            $order_year = date("Y", strtotime($value->datepaid));
            $current_day = date("d");
            $current_month = date("m");
            $current_year = date("Y");

            if (is_null($configEntity) && !is_array($configEntity)) {
                $invoiceList[$value->id]["open"] = 'false';
            }

            $arr = explode('/', $configEntity['activateDate']);

            /* formatear la fecha a dd-mm-aaaa porque la fecha datepaid
            tiene ese formato en WHMCS y deben tener el mismo formato para
            compararse. */
            $newDate = $arr[0] . '-' . $arr[1] . '-' . $arr[2];

            $activateDate = strtotime($newDate); //1 septiembre 2015
            $paidDate = strtotime($value->datepaid); //6 Octubre 2015

            // Validate plugin activation date vs payment date
            if ($paidDate < $activateDate) {
                $invoiceList[$value->id]["open"] = false;
            }

            //Validate invoice total is not zero
            if ($value->total <= 0) {
                $invoiceList[$value->id]["open"] = false;
            }

            //vamos sobre el tiempo de tolerancia para facturar.
            Carbon::setLocale('es');
            $strtotime = strtotime($value->datepaid);
            $fpago = explode("-", date("Y-m-d", $strtotime));
            $today = Carbon::now();



            if(is_null($strtotime) || $strtotime < 1) {
                $dt = Carbon::createFromDate(2018, 01, 01);
            } else {
                $dt = Carbon::createFromDate($fpago[0], $fpago[1], $fpago[2]);
            }

            //validamos que este dentro del rango para habilitar el boton de facturar
            if($dt->month < $today->month && $configEntity['DayOff'] !== 0) {
                if(($today->day > $configEntity['DayOff'] || ($today->month - $dt->month) > 1) || $dt->year !== $today->year) {
                    $invoiceList[$value->id]["open"] = false;
                }
            } else if($dt->year !== $today->year) {
                $invoiceList[$value->id]["open"] = false;
            }

        }

        $facturaInvoices = $this->getInvoicesFacturacom($UserID, $NumInvoices)['data'];

        foreach ($facturaInvoices as $key => $value) {
            $facturaInvoiceList[$value['NumOrder']] = $value;
            if (array_key_exists($value['NumOrder'], $invoiceList)) {
                $invoiceList[$value['NumOrder']]["sent"] = true;
            }
        }

        $collection = array_diff_key($invoiceList, $facturaInvoiceList);
        return $collection;
    }

    public function getInvoicesFacturacom($UserID, $Pedidos)
    {
        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);

        if (!is_null($Pedidos)) {
			$uri = $uri_base . 'v3/cfdi40/list?type_document=factura&client_reference=' . $UserID;
		} else {
			$uri = $uri_base . 'v3/cfdi40/list?type_document=factura&client_reference=' . $UserID . '&pedidos=' . base64_encode(implode(",", $Pedidos));
		}

        $invoices_filtred = [];

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->get($uri, [
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        return $request;
    }

    public function getInvoiceItems($invoiceId)
    {
        $Setting = $this->getGonfiguration();

        $itemsObj = Capsule::table('tblinvoiceitems')
            ->select("tblinvoiceitems.*", "tblhosting.id as hosting", "tblproducts.id as product", "tblhosting.packageid as package")
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->leftJoin('tblhosting', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
            ->leftJoin('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblinvoiceitems.invoiceid', $invoiceId)
            ->get();

        $itemsOrder = [];

        foreach ($itemsObj as $key => $value) {
            # code...
            $itemsOrder[$key] = $value;

            $configSat = Capsule::table('tblproductconfiggroups')
                ->select("tblproductconfigoptions.optionname as Nombre", "tblproductconfigoptionssub.optionname as Valor")
                ->join('tblproductconfiglinks', 'tblproductconfiggroups.id', '=', 'tblproductconfiglinks.gid')
                ->join('tblproductconfigoptions', 'tblproductconfigoptions.gid', '=', 'tblproductconfiggroups.id')
                ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.configid', '=', 'tblproductconfigoptions.id')
                ->where('tblproductconfiglinks.pid', $value->product)
                ->get();

            if(count($configSat) > 0) {

                foreach ($configSat as $ksat => $valsat) {
                    if ($valsat->Nombre == 'ClaveProdServ') {
                        $itemsOrder[$key]->ClaveProdServ = $valsat->Valor;
                    }

                    if ($valsat->Nombre == 'ClaveUnidad') {
                        $itemsOrder[$key]->ClaveUnidad = $valsat->Valor;
                    }

                    if ($valsat->Nombre == 'Unidad') {
                        $itemsOrder[$key]->Unidad = $valsat->Valor;
                    }
                }

            } else {
                $itemsOrder[$key]->ClaveProdServ = $Setting['ClaveProdServ'];
                $itemsOrder[$key]->ClaveUnidad = $Setting['ClaveUnidad'];
                $itemsOrder[$key]->Unidad = $Setting['Unidad'];
            }


        }

        return $itemsOrder;
    }

    public function getClientFacturacom($rfc)
    {

        if (!isset($rfc)) {
            return array(
                'Error' => 'No se ha recibido el RFC del cliente.',
            );
        }

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);
        $uri = $uri_base . 'v1/clients/' . $rfc;

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->get($uri, [
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        return $request;
    }

    public function getInvoicesFacturacomAll()
    {

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);
        $uri = $uri_base . 'v3/cfdi40/list?type_document=factura';

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->get($uri, [
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        return $request;
    }

    public function sendClientFacturacom($params, $clientUID = false)
    {

        if (!isset($params)) {
            return array(
                'response' => 'error',
                'message' => 'Indica los parametros del cliente',
            );
        }

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);

        if ($clientUID === false || $clientUID == "false") {
            $uri = $uri_base . 'v1/clients/create';
        } else {
            $uri = $uri_base . 'v1/clients/' . $clientUID . '/update';
        }

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->post($uri, [
            'json' => $params,
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        return $request;
    }

    public function getSystemURL()
    {
        $systemURL = Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')
            ->first();

        return $systemURL->value;
    }

    public function getLocation($cp)
    {

        if (!isset($cp)) {
            return array(
                'Error' => 'No se ha recibido el Código Postal.',
            );
        }

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);
        $uri = $uri_base . 'v3/getCodPos?cp=' . $cp;

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->get($uri, [
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        return $request;
    }

    /**
     * Update client information and create Invoice
     *
     * @param Int $orderNum
     * @param Array $orderItems
     * @param Array $clientData
     * @param String $serieInvoices
     * @param Int $clientW
     * @param String $paymentMethod
     * @return Array
     */
    public function createInvoice($orderNum, $orderItems, $clientData, $serieInvoices, $clientW, $paymentMethod, $numerocuenta, $usoCFDI)
    {

        /*if ($clientData['clientUID'] == "") {
        return array(
        'Error' => 'No se ha recibido el UID del cliente.',
        );
        }*/

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);
        $clientUID = $clientData["clientUID"] ?: false;
        $clientRFC = $clientData['fiscal-rfc'];
        $invoiceData = [];
        $Descuento = 0;
        $total = 0;

        //preparamos la inserción de cliente
    		$params = array(
    			'nombre' => $clientData["general-nombre"],
    			'apellidos' => $clientData["general-apellidos"],
    			'email' => $clientData["general-email"],
    			'telefono' => $clientData["fiscal-telefono"],
    			'razons' => htmlspecialchars_decode($clientData["fiscal-nombre"]),
    			'rfc' => $clientData["fiscal-rfc"],
                'regimen' => $clientData["fiscal-regimen"],
    			'calle' => $clientData["fiscal-calle"],
    			'numero_exterior' => $clientData["fiscal-exterior"],
    			'numero_interior' => $clientData["fiscal-interior"],
    			'codpos' => $clientData["fiscal-cp"],
    			'colonia' => $clientData["fiscal-colonia"],
    			'estado' => $clientData["fiscal-estado"],
    			'ciudad' => $clientData["fiscal-municipio"],
    			'delegacion' => $clientData["fiscal-municipio"],
    			'save' => true,
    			'client_reference' => $clientW,
    		);

    		//enviamos la info

    		$processClient = $this->sendClientFacturacom($params, $clientUID);
    		//print_r($processClient); die;

    		//validamos el proceso
    		if ($processClient->response == 'error') {
    			return [
    				'response' => 'error',
    				'message' => 'Ha ocurrido un error. Por favor revise sus datos e inténtelo de nuevo.',
    			];
    		}

		    $clientFactura = $processClient;
        $itemsCollection = $orderItems;
        $invoiceConcepts = [];
        //print_r($orderItems); die;

        //Adding concepts to invoice
        foreach ($itemsCollection as $value) {
            $productPrice = 0;
            $TipoFactor = 'Tasa';
            $TasaOCuota = 0.16;

            if ($Setting["IVA"] == 'on') {
                $productPrice = $value->amount / 1.16;
                $decimas = explode(".", $productPrice);

                //verificamos que no exceda el máximo de decimales
                if(strlen($decimas[1]) > 6) {
                    $productPrice = round($productPrice, 6);
                }
            } else {
                $productPrice = $value->amount;
            }


            $importeImpuesto = round(($productPrice * 0.16), 6);

            //Para productos cero pesos
            if($importeImpuesto ==  0) {
                $productPrice = 0.01;
                $TipoFactor = 'Exento';
                $importeImpuesto = 0;
                $TasaOCuota = 0;
            } else if ($importeImpuesto < 0) {
				$Descuento += ($productPrice * -1);
				continue;
			}

            $product = [
                'ClaveProdServ' => $value->ClaveProdServ,
                'Cantidad' => '1',
                'ClaveUnidad' => $value->ClaveUnidad,
                'Unidad' => $value->Unidad,
                'ValorUnitario' => $productPrice,
                'Descripcion' => $value->description,
                'Descuento' => '0',
                'Impuestos' => [
                    'Traslados' => [
                        ['Base' => $productPrice, 'Impuesto' => '002', 'TipoFactor' => $TipoFactor, 'TasaOCuota' => $TasaOCuota, 'Importe' => $importeImpuesto],
                    ],
                ],
            ];

            array_push($invoiceConcepts, $product);

            $total += $productPrice;
        }

        if ($numerocuenta == '') {
            $num_cta = 'No Identificado';
        } else {
            $num_cta = $numerocuenta;
        }
        
        if($Descuento > 0){ // Si hay algun descuento se lo aplicamos
            
            if($Descuento >= $total){
                return ['response' => 'error', 'message' => 'La cantidad a descontar no puede ser mayor o igual que el total de los conceptos'];
            }

            $porcentaje_descuento = $Descuento / $total;
            $centavos_faltantes = 0;
            $contador = 0;

            foreach($invoiceConcepts as $kconept => $concept){

                $contador++;

                $importe = $concept['ValorUnitario'] * $concept['Cantidad'];
                $desc = $importe * $porcentaje_descuento;

                if($importe > $desc + $centavos_faltantes){
                    $desc += $centavos_faltantes;
                    $centavos_faltantes = 0;
                }

                $decimas = explode(".", $desc);
                
                // Verificamos que no exceda el máximo de decimales
                if(count($decimas) > 1){
                    if(strlen($decimas[1]) > 6) {

                        if(count($invoiceConcepts) == $contador && $centavos_faltantes == 0){
                            $desc = round($desc, 6);
                        } else {
                            $nuevoDesc = bcdiv($desc, '1', 6);
                            $centavos_faltantes += $desc - $nuevoDesc;
                            $desc = $nuevoDesc;
                        }
                    }
                }

                $invoiceConcepts[$kconept]['Descuento'] = $desc;

                // Recalculamos el impuesto
                foreach ($invoiceConcepts[$kconept]['Impuestos']['Traslados'] as $kt => $valtras) {
					$invoiceConcepts[$kconept]['Impuestos']['Traslados'][$kt]['Base'] = round($importe - $desc, 6);
					$invoiceConcepts[$kconept]['Impuestos']['Traslados'][$kt]['Importe'] = round((($importe - $desc) * 0.16), 2);
				}
            }


            //En este punto ya distribuimos el descuento hasta la 1x10^-6, los centavos despues de eso se redondean a 6 decimas en el ultimo concepto
            // Por ejemplo: Si aplicamos un descuento de 10 pesos a 3 conceptos...
            // Descuento del concepto #1: 3.333333
            // Descuento del concepto #2: 3.333333
            // Descuento del concepto #3: 3.333334            
        }
        

        $invoiceData = [
            "Receptor" => ["UID" => $clientFactura['Data']['UID']],
            "TipoDocumento" => "factura",
            "UsoCFDI" => $usoCFDI,
            "Redondeo" => 2,
            "Conceptos" => $invoiceConcepts,
            "numerocuenta" => $numerocuenta,
            "FormaPago" => $paymentMethod,
            "MetodoPago" => 'PUE',
            "Moneda" => "MXN",
            "NumOrder" => $orderNum,
            "Serie" => $serieInvoices,
            "EnviarCorreo" => 'true',
        ];

        $uri = $uri_base . 'v3/cfdi40/create';

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $createInvoice = $restApi->post($uri, [
            'json' => $invoiceData,
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        $response = $createInvoice;

        if($response['response'] == 'error' && is_array($response['message'])) {
            $response = ['response' => 'error', 'message' => $response['message']['message']];
        }

        return $response;
    }

    public function getCFDI($params)
    {

        if (!isset($params)) {
            return [
                'response' => 'error',
                'message' => 'No hemos recibido parametros para procesar',
            ];
        }

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);

        //verificamos version f
        if ($params['version'] == '4.0') {
            $uri = $uri_base . 'v3/cfdi40/' . $params['uid'] . '/' . $params['type'];
        } else {
            $uri = $uri_base . 'publica/invoice/' . $params['uid'] . '/' . $params['type'];
            return header("Location: " . $uri);
        }

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->get($uri, [
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ]);

        $filename = explode("=", $request->getHeader('Content-Disposition'));
        $filename = $filename[1];

        switch ($params['type']) {
            case 'xml':
                header('Content-disposition: attachment; filename="' . $filename . '"');
                header('Content-type: "text/xml"; charset="utf8"');
                echo $request->getBody();
                break;
            case 'pdf':

                header('Content-Type: application/pdf');
                header("Content-Transfer-Encoding: Binary");
                header('Content-disposition: attachment; filename=' . $filename);
                echo $request->getBody();
                break;
        }
    }

    public function sendInvoiceEmail($params)
    {

        if (!isset($params)) {
            return [
                'response' => 'error',
                'message' => 'No hemos recibido parametros para procesar',
            ];
        }

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);

        if ($params['version'] == '4.0') {
            $uri = $uri_base . 'v3/cfdi40/' . $params['uid'] . '/email';
        } else {
            $uri = $uri_base . 'v1/invoice/' . $params['uid'] . '/email';
        }

        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->get($uri, [
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ],
        ])->json();

        return $request;
    }

    public function cancelInvoice($params)
    {

        if (!isset($params)) {
            return [
                'response' => 'error',
                'message' => 'No hemos recibido parametros para procesar',
            ];
        }

        $Setting = $this->getGonfiguration();
        $uri_base = $this->getURL($Setting);

        if ($params['version'] == '4.0') {
            $uri = $uri_base . 'v3/cfdi40/' . $params['uid'] . '/cancel';
        } else {
            $uri = $uri_base . 'v1/invoice/' . $params['uid'] . '/cancel';
        }
        
        //Conectamos con api factura.com y tramos todas las facturas
        $restApi = new Client;
        $request = $restApi->post($uri, [
            'json' => $params,
            'headers' => [
                'F-API-KEY' => $Setting['ApiKey'],
                'F-SECRET-KEY' => $Setting['ApiSecret'],
                'F-PLUGIN' => '60db2b9bf9790c1f4826429aee141291a6090c37',
                'Content-Type' => 'application/json',
            ]
        ])->json();

        return $request;
    }

    public function InvoicesFromWhmcs($invoice) {
        // Set post values
        $postfields = array(
            'invoiceid' => $invoice,
        );

        //conectamos con api local y traemos datos del invoice
        $response = localAPI('GetInvoice', $postfields, $this->username);

        if($response['result'] == 'success') {
            $Client = $this->GetClientFromWhmcs($response['userid']);

            if($Client['result'] === 'success') {
                $response['ClientData'] = $Client;
            }
        }

        return $response;

    }

    public function GetClientFromWhmcs($userid) {
        // Set post values
        $postfields = array(
            'clientid' => $userid,
        );

        //traemos datos del cliente
        $response = localAPI('GetClientsDetails', $postfields, $this->username);

        return $response;

    }

    public function getUsoCFDI() {
		$usosCFDI = [
			'G01' => 'Adquisición de mercancias',
			'G02' => 'Devoluciones, descuentos o bonificaciones',
			'G03' => 'Gastos en general',
			'I01' => 'Construcciones',
			'I02' => 'Mobilario y equipo de oficina por inversiones',
			'I03' => 'Equipo de transporte',
			'I04' => 'Equipo de computo y accesorios',
			'I05' => 'Dados, troqueles, moldes, matrices y herramental',
			'I06' => 'Comunicaciones telefónicas',
			'I07' => 'Comunicaciones satelitales',
			'I08' => 'Otra maquinaria y equipo',
			'D01' => 'Honorarios médicos, dentales y gastos hospitalarios.',
			'D02' => 'Gastos médicos por incapacidad o discapacidad',
			'D03' => 'Gastos funerales.',
			'D04' => 'Donativos.',
			'D05' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).',
			'D06' => 'Aportaciones voluntarias al SAR.',
			'D07' => 'Primas por seguros de gastos médicos.',
			'D08' => 'Gastos de transportación escolar obligatoria.',
			'D09' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.',
			'D10' => 'Pagos por servicios educativos (colegiaturas)',
			'S01' => 'Sin efectos fiscales',
		];

		return $usosCFDI;
	}

    public function getRegimenesFiscales() {
		$regimenes = [
            '601' => 'General de Ley Personas Morales',
            '603' => 'Personas Morales con Fines no Lucrativos',
            '605' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => 'Arrendamiento',
            '607' => 'Régimen de Enajenación o Adquisición de Bienes',
            '608' => 'Demás ingresos',
            '610' => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
            '611' => 'Ingresos por Dividendos (socios y accionistas)',
            '612' => 'Personas Físicas con Actividades Empresariales y Profesionales',
            '614' => 'Ingresos por intereses',
            '615' => 'Régimen de los ingresos por obtención de premios',
            '620' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
            '621' => 'Incorporación Fiscal',
            '622' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => 'Opcional para Grupos de Sociedades',
            '624' => 'Coordinados',
            '625' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626' => 'Régimen Simplificado de Confianza'
        ];

		return $regimenes;
    }
}
