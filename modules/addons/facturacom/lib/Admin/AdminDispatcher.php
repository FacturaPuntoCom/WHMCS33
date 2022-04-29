<?php

namespace WHMCS\Module\Addon\Facturacom\Admin;

/**
 * Sample Admin Area Dispatch Handler
 */
class AdminDispatcher {

    /**
     * Dispatch request.
     *
     * @param string $action
     * @param array $parameters
     *
     * @return string
     */
    public function dispatch($action, $parameters)
    {
        if (!$action) {
            // Default to index if no action specified
            $action = 'index';
        }

        $controller = new Controller();
        $uri_base = $this->setURL($parameters);

        // Verify requested action is valid and callable
        if (is_callable(array($controller, $action))) {
            return $controller->$action($parameters, $uri_base);
        }

        return '<p>Acción invalida, intenta de nuevo</p>';
    }

    private function setURL($parameters)
    {
        if($parameters['sandbox'] === 'on') {
            return 'https://sandbox.factura.com/api/v3/';
        } else {
            return 'https://factura.com/api/v3/';
        }
    }
}
