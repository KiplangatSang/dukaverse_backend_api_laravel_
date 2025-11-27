<?php
namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Payment Gateways",
 *     description="Manage ecommerce payment gateways"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class PaymentGatewayController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/payment-gateways",
     *     tags={"Payment Gateways"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all payment gateways",
     *     @OA\Response(
     *         response=200,
     *         description="List of payment gateways",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Stripe"),
     *                 @OA\Property(property="code", type="string", example="stripe"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-20T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T08:45:00Z")
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $gateways = PaymentGateway::all();
        return response()->json($gateways, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payment-gateways",
     *     tags={"Payment Gateways"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new payment gateway",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Stripe"),
     *             @OA\Property(property="description", type="string", example="Stripe payment gateway"),
     *             @OA\Property(
     *                 property="meta_data",
     *                 type="object",
     *                 @OA\Property(property="api_key", type="string")
     *             ),
     *             @OA\Property(property="logo", type="string", example="/uploads/stripe-logo.png"),
     *             @OA\Property(property="icon", type="string", example="fa-credit-card"),
     *             @OA\Property(property="regulation", type="string", example="PCI DSS compliant"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment gateway created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=10),
     *             @OA\Property(property="name", type="string", example="Stripe"),
     *             @OA\Property(property="description", type="string", example="Stripe payment gateway"),
     *             @OA\Property(
     *                 property="meta_data",
     *                 type="object",
     *                 @OA\Property(property="api_key", type="string")
     *             ),
     *             @OA\Property(property="logo", type="string", example="/uploads/stripe-logo.png"),
     *             @OA\Property(property="icon", type="string", example="fa-credit-card"),
     *             @OA\Property(property="regulation", type="string", example="PCI DSS compliant"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:34:56Z")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ownerable_type'   => 'nullable|string',
            'ownerable_id'     => 'nullable|integer',
            'gatewayable_type' => 'nullable|string',
            'gatewayable_id'   => 'nullable|integer',
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'meta_data'        => 'nullable|string',
            'logo'             => 'nullable|string',
            'icon'             => 'nullable|string',
            'regulation'       => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        $gateway = PaymentGateway::create($validated);
        return response()->json($gateway, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payment-gateways/{id}",
     *     tags={"Payment Gateways"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a single payment gateway",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the payment gateway"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment gateway data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Stripe"),
     *             @OA\Property(property="description", type="string", example="Stripe payment gateway"),
     *             @OA\Property(
     *                 property="meta_data",
     *                 type="object",
     *                 @OA\Property(property="api_key", type="string")
     *             ),
     *             @OA\Property(property="logo", type="string", example="/uploads/stripe-logo.png"),
     *             @OA\Property(property="icon", type="string", example="fa-credit-card"),
     *             @OA\Property(property="regulation", type="string", example="PCI DSS compliant"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-25T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-25T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment gateway not found"
     *     )
     * )
     */

    public function show($id)
    {
        $gateway = PaymentGateway::find($id);
        if (! $gateway) {
            return response()->json(['message' => 'Payment gateway not found'], 404);
        }
        return response()->json($gateway, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/payment-gateways/{id}",
     *     tags={"Payment Gateways"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a payment gateway",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="PayPal"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment gateway updated"),
     *     @OA\Response(response=404, description="Payment gateway not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $gateway = PaymentGateway::find($id);
        if (! $gateway) {
            return response()->json(['message' => 'Payment gateway not found'], 404);
        }

        $validated = $request->validate([
            'name'        => 'string|max:255',
            'description' => 'nullable|string',
            'meta_data'   => 'nullable|string',
            'logo'        => 'nullable|string',
            'icon'        => 'nullable|string',
            'regulation'  => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $gateway->update($validated);
        return response()->json($gateway, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/payment-gateways/{id}",
     *     tags={"Payment Gateways"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a payment gateway",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Payment gateway deleted"),
     *     @OA\Response(response=404, description="Payment gateway not found")
     * )
     */
    public function destroy($id)
    {
        $gateway = PaymentGateway::find($id);
        if (! $gateway) {
            return response()->json(['message' => 'Payment gateway not found'], 404);
        }

        $gateway->delete();
        return response()->json(['message' => 'Payment gateway deleted successfully'], 200);
    }
}
