<?php

namespace App\Mail;

use App\Models\AccountingTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AccountingTransaction $transaction,
        string $pdfContent,
    ) {
        $this->subject("Invoice {$transaction->reference_number}")
            ->html(
                '<p>Dear '.e($transaction->customer_name).',</p><p>Please find invoice '
                .e($transaction->reference_number).' attached.</p>'
            )
            ->attachData($pdfContent, "invoice-{$transaction->reference_number}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
