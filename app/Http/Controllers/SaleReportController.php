<?php
namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SaleReportController extends Controller
{

  public function __construct(
        protected ApiResource $apiResource,
    ) {
        parent::__construct($apiResource);
    }
    //

    public function generatePDF($account)
    {

        $salesrepo = new SaleService($account);
        $salesdata = $salesrepo->salesMailData();
        $pdf       = PDF::loadView('emails.sold_items', compact('salesdata'));
        return $pdf->download('sales_report.pdf');
    }
}
