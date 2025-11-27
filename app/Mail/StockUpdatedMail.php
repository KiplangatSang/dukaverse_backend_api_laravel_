<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public $retail, public $stockdata)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $stockdata = $this->stockdata;

        // Generate and store the PDF

        $pdf = PDF::loadView('pdfs.stockupdated', compact('stockdata'));
        $pdfPath = 'stock_report.pdf';
        $fullPath = public_path($pdfPath);

        // Save the PDF
        $pdf->save($fullPath);

        // Use the storage path for attaching
        $email = $this->from('dukaverse@gmail.com', 'DukaVerse')
            ->subject($this->retail->name . ' Stock report for' . now()->format('Y/m/d H:i'))
            ->view('emails.stockupdated', compact('stockdata'))
            ->attach($fullPath, [
                'as' => 'Stock_Report.pdf',
                'mime' => 'application/pdf',
            ]);

        return $email;

    }

}
