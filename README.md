# WHMCS Addon de Factura.com versión 2.2.0

# Changelog

## [2.2.0] - 2019-01-15

### Fixed
- Se corrige panel de administración ahora muestra correctamente la razon social del receptor.

## [2.1.4] - 2018-11-23

### Fixed
- Se corrigió la validación que permite traer facturas cotejandolas con pedidos.

### Fixed
- Verifica compras de meses pasados y bloquea su facturación.

## [2.1.1] - 2018-10-16

### Fixed
- Se solucionó el problema para validar pedidos que pueden ser facturados dentro de un rango de dias después del mes de compra.


## [2.1] - 2018-07-19

### Added
- Se agregó el soporte para productos con descuento.


## [2.0.8] - 2018-03-20

### Added
- Multiple RFC para facturar.
- UsoCFDI ahora el usuario podrá elegí el uso de su cfdi.
- Ahora los conceptos negativos son tomados como descuentos que se aplican al CFDI

### Fixed
- Fix actualización de datos de cliente.

## [2.0.7] - 2018-02-06

### Fixed
- Fix decimales cuando se resta el iva del producto.

## [2.0.6] - 2018-02-06

### Added
- Pagindador a pedidos sin facturar.


## [2.0.5] - 2018-02-02

### Fixed
- Fix productos en 0 pesos.
- Fix domicilio calle guardaba razón social.

# Changelog

## [2.0.4] - 2018-01-12

### Fixed
- Ahora los mesajes de error en el xml son mostrados en pantalla.
- Se eliminó la redirección cuando hay un error.

## [2.0.3] - 2018-01-11

### Fixed
- Ahora si intentas ir a facturacion.php y no haz iniciado sesión automáticamente te envía al login.
- Se corrigió el problema con orders que no tenían fecha de pago.

## [2.0.2] - 2018-01-10

### Added
- Ahora puedes facturar pedidos customizados es decir cuando no fue creado con un producto.
- Hemos actualizado el manual con nuevas configuraciones para poder facturar pedidos customizados.  [clic aquí](https://facturacom.kayako.com/article/77-instalacion-de-plugin-de-factura-com-para-whmcs)

## [2.0.1] - 2018-01-09

### Changed
- Ahora se pueden descargar los cfdi 3.2
- Facturas fuera de fecha y tolerancia no podrán facturarse.

### Plugin WHMCS

WHMCS es una solución todo en uno para la administración de clientes, pagos y soporte técnico para empresas
online.
El módulo Factura.com proporciona integración con la plataforma de **Factura.com** incluyendo las siguientes
funciones:
- Compatibilidad con CFDI 3.3.
- Reporte de facturas enviadas y canceladas en el panel de administración.
- Enviar facturas por email a los clientes automáticamente y cancelar facturas desde el panel de
administración.
- Funcionalidad para que los clientes creen facturas directamente desde el área de clientes.
- Reporte de historial de facturas y pedidos pendientes de facturar.

El módulo de Factura.com para WHMCS versión **V7.x**

Si requieres más información acerca de la instalación y el uso de este módulo para WHMCS, puedes contactarnos en https://factura.com/contacto o enviar un correo directamente a soporte@factura.com y con gusto te ayudaremos en lo que necesites.

## Instalación
Para su instalación sigue el siguiente manual: [clic aquí](https://facturacom.kayako.com/article/77-instalacion-de-plugin-de-factura-com-para-whmcs)

## Ver Demo

[http://whmcs.integrafactura.xyz](http://whmcs.integrafactura.xyz)
