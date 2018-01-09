<style media="screen">
{literal}
    .label-danger {
        background-color: #d9534f;
    }
    .label-success {
        background-color: #5cb85c;
    }
    table.dataTable thead th {
        background-color: #2A5E90;
        color: #FFFFFF;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        background: #2A5E90;
        color: #FFFFFF !important;
        border-color: #46b8da;
    }
{/literal}
</style>
<input type="hidden" id="systemURL" value="{$systemURL}" />
<p class="text-msg">
     {$invoices.total}  facturas en sistema.
</p>
<div class="tablebg">
    <table id="adminInvoices" width="100%" cellspacing="1" cellpadding="3">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha de creación</th>
                <th>Receptor</th>
                <th>Núm. de cliente</th>
                <th>Núm. de pedido</th>
                <th>Monto total</th>
                <th>Estado</th>
                <th>PDF</th>
                <th>XML</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            {foreach $invoices.data as $invoice}
            <tr>
                <td>  {$invoice.Folio} </td>
                <td>  {$invoice.FechaTimbrado} </td>
                <td>  {$invoice.Receptor} </td>
                <td><a href="{$systemURL}admin/clientssummary.php?userid={$invoice.ReferenceClient}" target="_blank">{$invoice.ReferenceClient}</a></td>
                <td><a href="{$systemURL}admin/invoices.php?action=edit&id={$invoice.NumOrder}" target="_blank">{$invoice.NumOrder}</a></td>
                <td>  $ {$invoice.Total|number_format:2:".":","} </td>
                <td><span class="{if $invoice.Status eq 'Cancelada'}alert-danger{else}alert-success{/if}">  {$invoice.Status} </span></td>
                <td><a href="{$systemURL}modules/addons/facturacom/lib/downloadhandler.php?uid={$invoice.UID}&type=pdf&version={$invoice.Version}" target="_blank">PDF</a></td>
                <td><a href="{$systemURL}modules/addons/facturacom/lib/downloadhandler.php?uid={$invoice.UID}&type=xml&version={$invoice.Version}" target="_blank">XML</a></td>
                <td>
                   {if $invoice.Status eq 'enviada'}
                    <a href="#" class="btn-send-email btn btn-info" data-uid="{$invoice.UID}" data-version="{$invoice.Version}">
                        <span class="glyphicon glyphicon-envelope"></span>
                        Enviar por correo
                    </a>
                    <a href="#" class="btn-cancel-invoice btn btn-danger" data-uid="{$invoice.UID}" data-version="{$invoice.Version}">
                        <span class="glyphicon glyphicon-ban-circle"></span>
                        Cancelar
                    </a>
                   {else}
                    &nbsp;
                   {/if}
                </td>
            </tr>
            {/foreach}

        </tbody>
    </table>
</div>
<div id="facturaModal" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="facturaModalLabel">Mensaje de Factura.com</h4>
            </div>
            <div class="modal-body" id="facturaModalText"></div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
             </div>
        </div>
    </div>
</div>
<div id="facturaModalConfirm" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="facturaModalLabel">Cancelar Factura.com</h4>
            </div>
            <div class="modal-body" id="facturaModalText">
                ¿Seguro que desea cancelar esta factura?
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary" id="cancelInvoiceBtn">Cencelar factura</button>
                <button type="button" data-dismiss="modal" class="btn">Conservar factura</button>
            </div>
        </div>
    </div>
</div>
