<?php
namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Leads",
 *     description="Manage marketing leads"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class LeadController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/leads",
     *     summary="Get all leads",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="campaign",
     *         in="query",
     *         description="Filter leads by campaign ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of leads fetched successfully"),
     *     @OA\Response(response=404, description="No leads found")
     * )
     */
    public function index($campaign = null)
    {
        $leads = $this->account()->leads()->with('campaign');

        if ($campaign) {
            $leads->where('campaign_id', $campaign);
        }

        $leads = $leads->get();

        if ($leads->isEmpty()) {
            return $this->sendError('No leads found', ["error" => $leads]);
        }

        return $this->sendResponse(["leads" => $leads], "Data fetched successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leads/create",
     *     summary="Get available lead status options",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of lead statuses")
     * )
     */
    public function create()
    {
        $leads_status = Lead::LEAD_STATUS_COLLECTION;

        if (empty($leads_status)) {
            return $this->sendError('Lead statuses not found', ["error" => $leads_status]);
        }

        return $this->sendResponse(["leads_status" => $leads_status], "Data fetched successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/leads",
     *     summary="Create a new lead",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","status"},
     *             @OA\Property(property="campaign_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="+254712345678"),
     *             @OA\Property(property="status", type="string", example="new")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Lead saved successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "campaign_id"  => ["nullable", "exists:campaigns,id"],
            "name"         => ["required", "string"],
            "email"        => ["nullable", "email"],
            "phone_number" => ["nullable", "string"],
            "status"       => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', ["errors" => $validator->errors()]);
        }

        if (! $request->email && ! $request->phone_number) {
            return $this->sendError('Validation failed', [
                "errors" => ["contact" => ["At least a phone number or email is required"]],
            ]);
        }

        $lead = $this->account()->leads()->create([
            "campaign_id"   => $request->campaign_id,
            "leadable_id"   => $request->campaign_id,
            "leadable_type" => $request->campaign_id ? Campaign::class : null,
            "user_id"       => $request->user_id ?? Auth::id(),
            "name"          => $request->name,
            "email"         => $request->email,
            "phone_number"  => $request->phone_number,
            "profile_url"   => $request->avatar ?? Lead::NO_PROFILE,
            "status"        => $request->status ? Lead::LEAD_STATUS_COLLECTION[$request->status] : Lead::LEAD_STATUS[0],
        ]);

        if (! $lead) {
            return $this->sendError('Failed to create lead');
        }

        return $this->sendResponse(["lead" => $lead->load("campaign")], "Lead saved successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leads/{id}",
     *     summary="Get a specific lead",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lead fetched successfully"),
     *     @OA\Response(response=404, description="Lead not found")
     * )
     */
    public function show($id)
    {
        $lead = Lead::with("campaign")->find($id);

        if (! $lead) {
            return $this->sendError('Lead not found');
        }

        return $this->sendResponse(["lead" => $lead], "Lead fetched successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leads/{id}/edit",
     *     summary="Get lead edit form data",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lead edit data fetched successfully"),
     *     @OA\Response(response=404, description="Lead not found")
     * )
     */
    public function edit($id)
    {
        $lead = Lead::with("campaign")->find($id);

        if (! $lead) {
            return $this->sendError('Lead not found');
        }

        return $this->sendResponse([
            "leads_status" => Lead::LEAD_STATUS,
            "lead"         => $lead,
        ], "Data fetched successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/leads/{id}",
     *     summary="Update an existing lead",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","status"},
     *             @OA\Property(property="campaign_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="email", type="string", example="updated@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="+254700000000"),
     *             @OA\Property(property="status", type="string", example="converted")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Lead updated successfully"),
     *     @OA\Response(response=404, description="Lead not found"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::find($id);

        if (! $lead) {
            return $this->sendError('Lead not found');
        }

        $validator = Validator::make($request->all(), [
            "campaign_id"  => ["nullable", "exists:campaigns,id"],
            "name"         => ["required", "string"],
            "email"        => ["nullable", "email"],
            "phone_number" => ["nullable", "string"],
            "status"       => ["required", "string"],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', ["errors" => $validator->errors()]);
        }

        if (! $request->email && ! $request->phone_number) {
            return $this->sendError('Validation failed', [
                "errors" => ["contact" => ["At least a phone number or email is required"]],
            ]);
        }

        $lead->update($request->all());

        return $this->sendResponse(["lead" => $lead->fresh()->load("campaign")], "Lead updated successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/leads/{id}",
     *     summary="Delete a specific lead",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lead deleted successfully"),
     *     @OA\Response(response=404, description="Lead not found")
     * )
     */
    public function destroy($id)
    {
        $deleted = Lead::destroy($id);

        if (! $deleted) {
            return $this->sendError('Failed to delete lead');
        }

        return $this->sendResponse(["lead" => $id], "Lead deleted successfully");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/campaigns/{campaign_id}/leads",
     *     summary="Delete all leads under a specific campaign",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="campaign_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="All leads deleted successfully"),
     *     @OA\Response(response=404, description="Campaign not found")
     * )
     */
    public function deleteAllCampaignLeads($campaign_id)
    {
        $campaign = Campaign::find($campaign_id);

        if (! $campaign) {
            return $this->sendError('Campaign not found');
        }

        $deleted = $campaign->leads()->delete();

        if (! $deleted) {
            return $this->sendError('Failed to delete campaign leads');
        }

        return $this->sendResponse([], "All leads deleted successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/v1/campaigns/{campaign_id}/leads/add",
     *     summary="Add leads to a campaign",
     *     tags={"Leads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="campaign_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Campaign loaded successfully"),
     *     @OA\Response(response=404, description="Campaign not found")
     * )
     */
    public function addLeadsToCampaign(Request $request, $campaign_id)
    {
        $campaign = Campaign::with(["leads", "user", "tasks", "comments"])->find($campaign_id);

        if (! $campaign) {
            return $this->sendError("Campaign not found");
        }

        // Placeholder: logic to add leads to campaign goes here
        return $this->sendResponse(["campaign" => $campaign], "Campaign loaded successfully");
    }
}
