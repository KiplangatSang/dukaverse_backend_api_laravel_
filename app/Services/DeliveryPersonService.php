<?php

namespace App\Services;

use App\Models\DeliveryPerson;
use App\Models\Delivery;
use App\Models\DeliveryEarning;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeliveryPersonService extends BaseService
{
    public function getIndexData(Request $request)
    {
        $query = DeliveryPerson::query();

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_online') && $request->is_online !== null) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        if ($request->has('is_verified') && $request->is_verified !== null) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        if ($request->has('is_accredited') && $request->is_accredited !== null) {
            $query->where('is_accredited', $request->boolean('is_accredited'));
        }

        if ($request->has('city') && $request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $deliveryPeople = $query->paginate($request->get('per_page', 15));

        return $deliveryPeople;
    }

    public function createDeliveryPerson(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['uuid'] = Str::uuid();

            // Handle file uploads
            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $this->storeFile($request->file('profile_photo'), 'delivery-people/profile-photos');
            }

            if ($request->hasFile('id_photo')) {
                $data['id_photo'] = $this->storeFile($request->file('id_photo'), 'delivery-people/id-photos');
            }

            if ($request->hasFile('license_photo')) {
                $data['license_photo'] = $this->storeFile($request->file('license_photo'), 'delivery-people/license-photos');
            }

            $deliveryPerson = DeliveryPerson::create($data);

            DB::commit();
            return $deliveryPerson->load(['verifiedBy', 'accreditedBy', 'createdBy']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getShowData($deliveryPersonId)
    {
        $deliveryPerson = DeliveryPerson::with([
            'deliveries',
            'earnings',
            'vehicles',
            'bids',
            'coupons',
            'verifiedBy',
            'accreditedBy',
            'createdBy',
            'updatedBy',
            'currentSubscription'
        ])->findOrFail($deliveryPersonId);

        // Calculate additional stats
        $stats = [
            'total_deliveries' => $deliveryPerson->total_deliveries,
            'completed_deliveries' => $deliveryPerson->completed_deliveries,
            'completion_rate' => $deliveryPerson->completion_rate,
            'total_earnings' => $deliveryPerson->total_earnings,
            'pending_payments' => $deliveryPerson->pending_payments,
            'current_rating' => $deliveryPerson->rating,
        ];

        return [
            'delivery_person' => $deliveryPerson,
            'stats' => $stats
        ];
    }

    public function updateDeliveryPerson(DeliveryPerson $deliveryPerson, Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            // Handle file uploads
            if ($request->hasFile('profile_photo')) {
                // Delete old file if exists
                if ($deliveryPerson->profile_photo) {
                    Storage::disk('public')->delete($deliveryPerson->profile_photo);
                }
                $data['profile_photo'] = $this->storeFile($request->file('profile_photo'), 'delivery-people/profile-photos');
            }

            if ($request->hasFile('id_photo')) {
                if ($deliveryPerson->id_photo) {
                    Storage::disk('public')->delete($deliveryPerson->id_photo);
                }
                $data['id_photo'] = $this->storeFile($request->file('id_photo'), 'delivery-people/id-photos');
            }

            if ($request->hasFile('license_photo')) {
                if ($deliveryPerson->license_photo) {
                    Storage::disk('public')->delete($deliveryPerson->license_photo);
                }
                $data['license_photo'] = $this->storeFile($request->file('license_photo'), 'delivery-people/license-photos');
            }

            $deliveryPerson->update($data);

            DB::commit();
            return $deliveryPerson->fresh(['verifiedBy', 'accreditedBy', 'createdBy', 'updatedBy']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteDeliveryPerson(DeliveryPerson $deliveryPerson)
    {
        DB::beginTransaction();
        try {
            // Delete associated files
            if ($deliveryPerson->profile_photo) {
                Storage::disk('public')->delete($deliveryPerson->profile_photo);
            }
            if ($deliveryPerson->id_photo) {
                Storage::disk('public')->delete($deliveryPerson->id_photo);
            }
            if ($deliveryPerson->license_photo) {
                Storage::disk('public')->delete($deliveryPerson->license_photo);
            }

            $deliveryPerson->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function verifyDeliveryPerson(DeliveryPerson $deliveryPerson, $verifier = null)
    {
        $deliveryPerson->verify($verifier);
        return $deliveryPerson->fresh();
    }

    public function accreditDeliveryPerson(DeliveryPerson $deliveryPerson, $accrediter = null)
    {
        $deliveryPerson->accredit($accrediter);
        return $deliveryPerson->fresh();
    }

    public function updateStatus(DeliveryPerson $deliveryPerson, string $status)
    {
        $deliveryPerson->updateStatus($status);
        return $deliveryPerson->fresh();
    }

    public function getEarnings(DeliveryPerson $deliveryPerson, Request $request)
    {
        $query = $deliveryPerson->earnings();

        // Apply filters
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('period') && $request->period) {
            $query->where('period', $request->period);
        }

        if ($request->has('start_date') && $request->end_date) {
            $query->whereBetween('earning_date', [$request->start_date, $request->end_date]);
        }

        $earnings = $query->with(['delivery'])
                          ->orderBy('earning_date', 'desc')
                          ->paginate($request->get('per_page', 15));

        $summary = [
            'total_earned' => $deliveryPerson->earnings()->sum('total_earned'),
            'total_deductions' => $deliveryPerson->earnings()->sum('total_deductions'),
            'net_earnings' => $deliveryPerson->earnings()->sum('net_earnings'),
            'paid_amount' => $deliveryPerson->earnings()->where('payment_status', 'paid')->sum('net_earnings'),
            'pending_amount' => $deliveryPerson->earnings()->where('payment_status', 'pending')->sum('net_earnings'),
        ];

        return [
            'earnings' => $earnings,
            'summary' => $summary
        ];
    }

    public function getDeliveries(DeliveryPerson $deliveryPerson, Request $request)
    {
        $query = $deliveryPerson->deliveries();

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $deliveries = $query->with(['customer', 'restaurant', 'earnings'])
                           ->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 15));

        return $deliveries;
    }

    public function setOnline(DeliveryPerson $deliveryPerson)
    {
        $deliveryPerson->goOnline();
        return $deliveryPerson->fresh();
    }

    public function setOffline(DeliveryPerson $deliveryPerson)
    {
        $deliveryPerson->goOffline();
        return $deliveryPerson->fresh();
    }

    public function setAvailable(DeliveryPerson $deliveryPerson, bool $available)
    {
        $deliveryPerson->setAvailable($available);
        return $deliveryPerson->fresh();
    }

    private function storeFile($file, $path)
    {
        return $file->store($path, 'public');
    }
}
