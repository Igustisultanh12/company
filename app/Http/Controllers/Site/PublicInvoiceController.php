<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\InvoiceView;
use Illuminate\Contracts\View\View;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice): View
    {
        abort_if(in_array($invoice->status, ['draft'], true), 404);

        return InvoiceView::make($invoice, false);
    }
}
