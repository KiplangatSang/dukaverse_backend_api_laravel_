<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanApplicationRequest;
use App\Http\Requests\UpdateLoanApplicationRequest;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Repositories\ThirdPartyRepository;

/**
 * @OA\Tag(
 *     name="Loan Applications",
 *     description="Manage loan applications and payments"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class LoanApplicationController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/loans/{loan}/applications",
     *     summary="Get loan application details",
     *     description="Fetch the latest loan application for a given loan and return status details",
     *     tags={"Loan Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="loan",
     *         in="path",
     *         required=true,
     *         description="Loan ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Loan application data fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No loan application found"
     *     )
     * )
     */
    public function index(Loan $loan)
    {
        // Get the first loan application for this loan (or latest one)
        $loanApplication = $loan->loanApplications()->latest()->first();

        if (! $loanApplication) {
            return $this->sendError('No loan application found');
        }

        $styling = ['loan_status_color' => ''];

        // Determine loan status display and styling
        switch ($loanApplication->loan_status) {
            case -1:
                $loanApplication->loan_status        = "Waiting";
                $loanApplication->loan_assigned_at   = "N/A";
                $loanApplication->loan_assigned_by   = "N/A";
                $loanApplication->loan_repaid_amount = "N/A";
                $styling['loan_status_color']        = "text-danger";
                break;

            case 0:
                $loanApplication->loan_status = "Processed";
                $styling['loan_status_color'] = "text-info";
                break;

            default:
                $loanApplication->loan_status = "Paid";
                $styling['loan_status_color'] = "text-success";
                break;
        }

        $loanApplication->loan_duration .= " Days";

        return $this->sendResponse([
            'loan'             => $loan,
            'loan_application' => $loanApplication,
            'styling'          => $styling,
        ], "Loan application data fetched successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/loan-applications",
     *     summary="Create a new loan application",
     *     description="Stores a new loan application in the system",
     *     tags={"Loan Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "loan_id": 1,
     *                 "loan_amount": 10000,
     *                 "loan_duration": 30
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Loan application created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to create loan application"
     *     )
     * )
     */
    public function store(StoreLoanApplicationRequest $request)
    {
        $loanApplication = LoanApplication::create($request->validated());

        if (! $loanApplication) {
            return $this->sendError('Failed to create loan application');
        }

        return $this->sendResponse(
            ['loan_application' => $loanApplication],
            "Loan application created successfully"
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/loan-applications/{loanApplication}",
     *     summary="Get a specific loan application",
     *     tags={"Loan Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="loanApplication",
     *         in="path",
     *         required=true,
     *         description="Loan Application ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Loan application fetched successfully"
     *     )
     * )
     */
    public function show(LoanApplication $loanApplication)
    {
        return $this->sendResponse(
            ['loan_application' => $loanApplication],
            "Loan application fetched successfully"
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/loan-applications/{loanApplication}",
     *     summary="Update a loan application",
     *     tags={"Loan Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="loanApplication",
     *         in="path",
     *         required=true,
     *         description="Loan Application ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             example={"loan_amount": 12000}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Loan application updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to update loan application"
     *     )
     * )
     */
    public function update(UpdateLoanApplicationRequest $request, LoanApplication $loanApplication)
    {
        $updated = $loanApplication->update($request->validated());

        if (! $updated) {
            return $this->sendError('Failed to update loan application');
        }

        return $this->sendResponse(
            ['loan_application' => $loanApplication->fresh()],
            "Loan application updated successfully"
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/loan-applications/{loanApplication}",
     *     summary="Delete a loan application",
     *     tags={"Loan Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="loanApplication",
     *         in="path",
     *         required=true,
     *         description="Loan Application ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Loan application deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to delete loan application"
     *     )
     * )
     */
    public function destroy(LoanApplication $loanApplication)
    {
        $deleted = $loanApplication->delete();

        if (! $deleted) {
            return $this->sendError('Failed to delete loan application');
        }

        return $this->sendResponse([], "Loan application deleted successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/loan-applications/{loanApplicationId}/pay-request",
     *     summary="Get loan payment request details",
     *     tags={"Loan Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="loanApplicationId",
     *         in="path",
     *         required=true,
     *         description="Loan Application ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Loan payment data fetched successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Loan application not found"
     *     )
     * )
     */
    public function payLoanRequest($loanApplicationId)
    {
        $loanApplication = LoanApplication::find($loanApplicationId);

        if (! $loanApplication) {
            return $this->sendError('Loan application not found');
        }

        $thirdPartyRepo   = new ThirdPartyRepository();
        $thirdPartyImages = $thirdPartyRepo->getThirdPartyImages();

        return $this->sendResponse([
            'third_party_images' => $thirdPartyImages,
            'loan_application'   => $loanApplication,
        ], "Loan payment data fetched successfully");
    }
}
