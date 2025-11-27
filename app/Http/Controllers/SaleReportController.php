<?php
namespace App\Http\Controllers;

use App\Repositories\SalesRepository;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SaleReportController extends Controller
{
    //

    public function generatePDF($account)
    {

        $salesrepo = new SalesRepository($account);
        $salesdata = $salesrepo->salesMailData();
        $pdf       = PDF::loadView('emails.sold_items', compact('salesdata'));
        return $pdf->download('sales_report.pdf');
    }
}
