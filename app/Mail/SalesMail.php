<?php
namespace App\Mail;

use App\Http\Controllers\BaseController;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public $sales, public $user)
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

        $saveLocally = true;
        $file_type   = "pdfs";

        $salesdata = $this->sales;

        $baseController = new BaseController();

        $newPDF = null;
        if ($saveLocally) {
            $newPDF = $baseController->savePDFFileLocally($this->user, $file_type, "sales_report.pdf");
        } else {
            $newPDF = $baseController->savePDFFileRemotely($this->user, $file_type, "sales_report.pdf");

        }

        // Generate and store the PDF

        $pdf      = PDF::loadView('pdfs.sold_items', compact('salesdata'));
        $fullPath = $newPDF; // Full path to save
                             // Save the PDF
        $pdf->save($fullPath);

        // Use the storage path for attaching
        $email = $this->from('dukaverse@gmail.com', 'DukaVerse')
            ->subject('SALES MADE ON ' . now()->format('Y/m/d H:i'))
            ->view('emails.sold_items', compact('salesdata'))
            ->attach($fullPath, [
                'as'   => 'SalesReport.pdf', // The name the file will have in the email
                'mime' => 'application/pdf', // MIME type for PDF
            ]);

        return $email;

    }

/*
{
    $saveLocally = true;
    $salesdata   = $this->sales;

// Generate and store the PDF

    $pdf      = PDF::loadView('pdfs.sold_items', compact('salesdata'));
    $pdfPath  = 'sales_report.pdf';    // Filename to store
    $fullPath = public_path($pdfPath); // Full path to save

// Save the PDF
    $pdf->save($fullPath);

// Use the storage path for attaching
    $email = $this->from('dukaverse@gmail.com', 'DukaVerse')
        ->subject('SALES MADE ON ' . now()->format('Y/m/d H:i'))
        ->view('emails.sold_items', compact('salesdata'))
        ->attach($fullPath, [
            'as'   => '/pdfs/Sales_Report.pdf', // The name the file will have in the email
            'mime' => 'application/pdf',        // MIME type for PDF
        ]);

    return $email;

}

*/

}
