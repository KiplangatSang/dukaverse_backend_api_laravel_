<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\AccreditationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccreditationController extends BaseController
{
    public function __construct(
        private readonly AccreditationService $accreditationService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/accreditation/apply",
     *     summary="Apply for accreditation",
     *     tags={"Accreditation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"business_type","years_experience","specializations","references"},
     *             @OA\Property(property="business_type", type="string", enum={"individual","business"}),
     *             @OA\Property(property="years_experience", type="integer"),
     *             @OA\Property(property="specializations", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="references", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Accreditation application submitted successfully"
     *     )
     * )
     */
    public function apply(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'business_type' => 'required|string|in:individual,business',
            'years_experience' => 'required|integer|min:0',
            'specializations' => 'required|array',
            'specializations.*' => 'string',
            'references' => 'required|array',
            'references.*.name' => 'required|string',
            'references.*.company' => 'required|string',
            'references.*.email' => 'required|email',
            'references.*.phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->accreditationService->apply($request->user(), $request->all());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }


        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accreditation/status",
     *     summary="Get accreditation status",
     *     tags={"Accreditation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accreditation status retrieved successfully"
     *     )
     * )
     */
    public function status(Request $request)
    {
        try{
        $result = $this->accreditationService->getStatus($request->user());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);

         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/accreditation/upload-document",
     *     summary="Upload accreditation documents",
     *     tags={"Accreditation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     @OA\Property(property="file", type="string", format="binary"),
     *                     @OA\Property(property="type", type="string", enum={"business_license","insurance","background_check","training_certificate"})
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document uploaded successfully"
     *     )
     * )
     */
    public function uploadDocument(Request $request)
    {
      try{  $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'type' => 'required|string|in:business_license,insurance,background_check,training_certificate',
        ]);

        if ($validator->fails()) {
            return $this->apiResource->error("Validation Error", 422, $validator->errors());
        }

        $result = $this->accreditationService->uploadDocument($request->user(), $request->file('file'), $request->type);

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accreditation/requirements",
     *     summary="Get accreditation requirements",
     *     tags={"Accreditation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accreditation requirements retrieved successfully"
     *     )
     * )
     */
    public function requirements()
    {
        try{
        $result = $this->accreditationService->getRequirements();
        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/accreditation/renew",
     *     summary="Renew accreditation",
     *     tags={"Accreditation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accreditation renewal initiated successfully"
     *     )
     * )
     */
    public function renew(Request $request)
    {
        try{
        $result = $this->accreditationService->renew($request->user());

        if (!$result['success']) {
            return $this->apiResource->error($result['message'], $result['httpCode']);
        }

        return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);

         } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accreditation/details",
     *     summary="Get accreditation service details",
     *     tags={"Service Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accreditation details retrieved successfully"
     *     )
     * )
     */
    public function details()
    {
        try {
            $result = $this->accreditationService->getDetails();
            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error($ex->getMessage(), 500);
        }
    }
}
