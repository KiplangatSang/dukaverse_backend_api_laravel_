<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserActivityRequest;
use App\Http\Requests\UpdateUserActivityRequest;
use App\Http\Resources\ApiResource;
use App\Models\UserActivity;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserActivityController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $activities = $this->account()->activities()->get();

        if (! $activities) {
            return $this->sendError('Data could not be fetched', ["wallets" => $activities]);
        }
        return $this->sendResponse(["activities" => $activities], "Data fetched successfully");

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $activity_type = UserActivity::ACTIVITY_TYPES;

        return $this->sendResponse(["activity_type" => $activity_type], "Data fetched successfully");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserActivityRequest $request)
    {
        //

        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return $this->sendError('Bad request', $validator->errors());
        }

        $userActivity = $this->account()->userActivity()->create([
            "name"              => $request->name,
            "user_id"           => $request->user_id ?? auth()->id(),
            "activityable_id"   => $request->activityable_id,
            "activityable_type" => $request->activityable_type,
            "activity_type"     => $request->activity_type,
            "operating_system"  => $request->operating_system,
            "ip_addresses"      => [$this->getIPAddress($request)],
            "meta_data"         => $this->jsonEncodeData($request->meta_data),
            "is_successs"       => $request->is_successs,

        ]);

        if (! $userActivity) {
            return $this->sendError('Data could not be created', ["userActivity" => $userActivity]);
        }

        return $this->sendResponse(["userActivity" => $userActivity], "userActivity created successfully");

    }

    /**
     * Display the specified resource.
     */
    public function show(UserActivity $userActivity)
    {
        //

        $userActivity = UserActivity::where('id', $userActivity)->first();

        if (! $userActivity) {
            return $this->sendError('Data could not be found', ["userActivity" => $userActivity]);
        }

        return $this->sendResponse(["userActivity" => $userActivity], "userActivity fetched successfully");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($userActivity)
    {
        //

        $userActivity = UserActivity::where('id', $userActivity)->first();

        if (! $userActivity) {
            return $this->sendError('Data could not be found', ["userActivity" => $userActivity]);
        }

        $activity_type = UserActivity::ACTIVITY_TYPES;

        return $this->sendResponse(["activity_type" => $activity_type, "userActivity" => $userActivity], "Data fetched successfully");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserActivityRequest $request, $userActivity)
    {
        //

        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return $this->sendError('Bad request', $validator->errors());
        }

        $userActivity = UserActivity::where('id', $userActivity)->first();

        if (! $userActivity) {
            return $this->sendError('Data could not be found', ["userActivity" => $userActivity]);
        }

        $userActivity = $userActivity->update([
            "name"              => $request->name,
            "user_id"           => $request->user_id ?? auth()->id(),
            "activityable_id"   => $request->activityable_id,
            "activityable_type" => $request->activityable_type,
            "activity_type"     => $request->activity_type,
            "operating_system"  => $request->operating_system,
            "ip_addresses"      => array_push($userActivity->ip_addresses, $this->getIPAddress($request)),
            "meta_data"         => $this->jsonEncodeData($request->meta_data),
            "is_successs"       => $request->is_successs,
        ]);

        if (! $userActivity) {
            return $this->sendError('Data could not be updated', ["userActivity" => $userActivity]);
        }

        return $this->sendResponse(["userActivity" => $userActivity], "userActivity updated successfully");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userActivity)
    {
        //
        $result = UserActivity::destroy($userActivity);

        if (! $result) {
            return $this->sendError('Data could not be found', ["result" => $userActivity]);
        }

        return $this->sendResponse(["result" => $result], "userActivity deleted successfully");
    }
}
