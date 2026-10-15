<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

class InvoiceView
{
    public static function make(Invoice $invoice, bool $isAdmin): View
    {
        $invoice->loadMissing(['payments', 'order:id,code']);

        return view('invoice', [
            'invoice' => $invoice,
            'isAdmin' => $isAdmin,
            'company' => [
                'name' => Setting::get('company_name'),
                'legal_type' => Setting::get('company_legal_type'),
                'address' => Setting::get('address'),
                'email' => Setting::get('email'),
                'phone' => Setting::get('phone') ?: Setting::get('whatsapp'),
                'nib' => Setting::get('legal_nib'),
            ],
            'bank' => [
                'name' => Setting::get('invoice_bank_name'),
                'account' => Setting::get('invoice_bank_account'),
                'holder' => Setting::get('invoice_bank_holder'),
            ],
            'footer' => Setting::get('invoice_footer'),
            'statuses' => Catalog::INVOICE_STATUSES,
        ]);
    }

    public static function rupiah(int|float|null $v): string
    {
        return 'Rp'.number_format((float) $v, 0, ',', '.');
    }
}
