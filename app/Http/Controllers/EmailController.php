<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\AutoEmail;
use App\Models\EmailConfig;
use App\Models\EmailNotification;
use App\Models\EmailSignature;
use App\Services\EmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    protected EmailService $emailService;
    public function __construct(
        protected ApiResource $apiResource,
        EmailService $emailService
    ) {
        parent::__construct($apiResource);
        $this->emailService = $emailService;
    }

    /**
     * Get all email configs
     */
    public function getConfigs()
    {
        try {
            $configs = EmailConfig::all();
            return response()->json($configs);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Create or update email config
     */
    public function storeConfig(Request $request)
    {
        try {
            $data = $request->validate([
                'client_name'     => 'required|string|unique:email_configs,client_name',
                'imap_host'       => 'required|string',
                'imap_port'       => 'integer',
                'imap_encryption' => 'string',
                'imap_username'   => 'required|string',
                'imap_password'   => 'required|string',
                'smtp_host'       => 'required|string',
                'smtp_port'       => 'integer',
                'smtp_encryption' => 'string',
                'smtp_username'   => 'required|string',
                'smtp_password'   => 'required|string',
                'from_email'      => 'required|email',
                'from_name'       => 'string|nullable',
                'active'          => 'boolean',
            ]);

            $config = EmailConfig::create($data);
            return response()->json($config, 201);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Update email config
     */
    public function updateConfig(Request $request, EmailConfig $config)
    {
        try {
            $data = $request->validate([
                'client_name'     => 'string|unique:email_configs,client_name,' . $config->id,
                'imap_host'       => 'string',
                'imap_port'       => 'integer',
                'imap_encryption' => 'string',
                'imap_username'   => 'string',
                'imap_password'   => 'string',
                'smtp_host'       => 'string',
                'smtp_port'       => 'integer',
                'smtp_encryption' => 'string',
                'smtp_username'   => 'string',
                'smtp_password'   => 'string',
                'from_email'      => 'email',
                'from_name'       => 'string|nullable',
                'active'          => 'boolean',
            ]);

            $config->update($data);
            return response()->json($config);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Get email notifications
     */
    public function getNotifications(Request $request)
    {
        try {
            $query = EmailNotification::with('emailConfig');

            if ($request->has('processed')) {
                $query->where('processed', $request->boolean('processed'));
            }

            $notifications = $query->paginate(50);
            return response()->json($notifications);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Send email
     */
    public function sendEmail(Request $request)
    {
        try {
            $data = $request->validate([
                'config_id' => 'required|exists:email_configs,id',
                'to'        => 'required|array',
                'to.*'      => 'email',
                'subject'   => 'required|string',
                'body'      => 'required|string',
            ]);

            $config = EmailConfig::find($data['config_id']);
            $this->emailService->sendEmail($data, $config);

            return response()->json(['message' => 'Email sent successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Mark notification as processed
     */
    public function markProcessed(EmailNotification $notification)
    {
        try {
            $notification->update(['processed' => true]);
            return response()->json($notification);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Get single email config
     */
    public function getConfig(EmailConfig $config)
    {
        try {
            return response()->json($config->load(['emailSignatures', 'autoEmails']));
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Delete email config
     */
    public function deleteConfig(EmailConfig $config)
    {
        try {
            $config->delete();
            return response()->json(['message' => 'Email config deleted successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Manual email check trigger
     */
    public function checkEmails(EmailConfig $config)
    {
        try {
            try {
                $this->emailService->readEmails($config);
                return response()->json(['message' => 'Email check completed successfully']);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to check emails: ' . $e->getMessage()], 500);
            }
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Bulk mark notifications as processed
     */
    public function bulkMarkProcessed(Request $request)
    {
        try {
            $request->validate([
                'notification_ids'   => 'required|array',
                'notification_ids.*' => 'exists:email_notifications,id',
            ]);

            EmailNotification::whereIn('id', $request->notification_ids)
                ->update(['processed' => true]);

            return response()->json(['message' => 'Notifications marked as processed']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Bulk delete notifications
     */
    public function bulkDeleteNotifications(Request $request)
    {
        try {
            $request->validate([
                'notification_ids'   => 'required|array',
                'notification_ids.*' => 'exists:email_notifications,id',
            ]);

            EmailNotification::whereIn('id', $request->notification_ids)->delete();

            return response()->json(['message' => 'Notifications deleted successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Get email statistics/analytics
     */
    public function getStatistics(Request $request)
    {
        try {
            $configId = $request->query('config_id');

            $query = EmailNotification::query();

            if ($configId) {
                $query->where('email_config_id', $configId);
            }

            $totalNotifications       = $query->count();
            $processedNotifications   = (clone $query)->where('processed', true)->count();
            $unprocessedNotifications = $totalNotifications - $processedNotifications;

            $todayNotifications = (clone $query)->whereDate('created_at', today())->count();
            $weekNotifications  = (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $monthNotifications = (clone $query)->whereMonth('created_at', now()->month)->count();

            return response()->json([
                'total_notifications'       => $totalNotifications,
                'processed_notifications'   => $processedNotifications,
                'unprocessed_notifications' => $unprocessedNotifications,
                'today_notifications'       => $todayNotifications,
                'week_notifications'        => $weekNotifications,
                'month_notifications'       => $monthNotifications,
            ]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Resend failed emails
     */
    public function resendEmail(Request $request)
    {
        try {
            $request->validate([
                'notification_id' => 'required|exists:email_notifications,id',
            ]);

            $notification = EmailNotification::find($request->notification_id);
            $config       = $notification->emailConfig;

            try {
                $this->emailService->sendEmail([
                    'to'      => $notification->to,
                    'subject' => $notification->subject,
                    'body'    => $notification->body,
                ], $config);

                return response()->json(['message' => 'Email resent successfully']);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to resend email: ' . $e->getMessage()], 500);
            }
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Archive notifications
     */
    public function archiveNotifications(Request $request)
    {
        try {
            $request->validate([
                'notification_ids'   => 'required|array',
                'notification_ids.*' => 'exists:email_notifications,id',
            ]);

            EmailNotification::whereIn('id', $request->notification_ids)
                ->update(['processed' => true]);

            return response()->json(['message' => 'Notifications archived successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Send email with attachments
     */
    public function sendEmailWithAttachments(Request $request)
    {
        try {
            $data = $request->validate([
                'config_id'     => 'required|exists:email_configs,id',
                'to'            => 'required',
                'subject'       => 'required|string',
                'body'          => 'required|string',
                'signature_id'  => 'nullable|exists:email_signatures,id',
                'attachments'   => 'array',
                'attachments.*' => 'file|max:10240', // 10MB max
            ]);

            // Handle 'to' field - can be array or JSON string
            $toEmails = $data['to'];
            if (is_string($toEmails)) {
                $toEmails = json_decode($toEmails, true);
                if (! is_array($toEmails)) {
                    return response()->json(['message' => 'Invalid "to" field format. Must be an array or JSON array string.'], 422);
                }
            }

            // Validate each email in the array
            foreach ($toEmails as $email) {
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json(['message' => "Invalid email address: {$email}"], 422);
                }
            }

            $data['to'] = $toEmails;

            $config = EmailConfig::find($data['config_id']);

            // Handle file uploads
            $uploadedFiles = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path            = $file->store('email_attachments', 'public');
                    $uploadedFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }

            $data['attachments'] = $uploadedFiles;

            $this->emailService->sendEmail($data, $config);

            return response()->json(['message' => 'Email sent successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Get email signatures
     */
    public function getSignatures(Request $request)
    {
        try {
            $configId = $request->query('config_id');
            $query    = EmailSignature::query();

            if ($configId) {
                $query->where('email_config_id', $configId);
            }

            $signatures = $query->where('active', true)->get();
            return response()->json($signatures);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Create email signature
     */
    public function createSignature(Request $request)
    {
        try {
            $data = $request->validate([
                'name'            => 'required|string',
                'content'         => 'required|string',
                'email_config_id' => 'required|exists:email_configs,id',
                'is_default'      => 'boolean',
                'active'          => 'boolean',
            ]);

            $signature = EmailSignature::create($data);
            return response()->json($signature, 201);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Update email signature
     */
    public function updateSignature(Request $request, EmailSignature $signature)
    {
        try {
            $data = $request->validate([
                'name'       => 'string',
                'content'    => 'string',
                'is_default' => 'boolean',
                'active'     => 'boolean',
            ]);

            $signature->update($data);
            return response()->json($signature);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Delete email signature
     */
    public function deleteSignature(EmailSignature $signature)
    {
        try {
            $signature->delete();
            return response()->json(['message' => 'Signature deleted successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Get auto emails
     */
    public function getAutoEmails(Request $request)
    {
        try {
            $configId = $request->query('config_id');
            $query    = AutoEmail::query();

            if ($configId) {
                $query->where('email_config_id', $configId);
            }

            $autoEmails = $query->get();
            return response()->json($autoEmails);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Create auto email
     */
    public function createAutoEmail(Request $request)
    {
        try {
            $data = $request->validate([
                'name'            => 'required|string',
                'trigger_event'   => 'required|string',
                'subject'         => 'required|string',
                'body'            => 'required|string',
                'email_config_id' => 'required|exists:email_configs,id',
                'conditions'      => 'array',
                'delay_minutes'   => 'integer|min:0',
                'active'          => 'boolean',
            ]);

            $autoEmail = AutoEmail::create($data);
            return response()->json($autoEmail, 201);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Update auto email
     */
    public function updateAutoEmail(Request $request, AutoEmail $autoEmail)
    {
        try {
            $data = $request->validate([
                'name'          => 'string',
                'trigger_event' => 'string',
                'subject'       => 'string',
                'body'          => 'string',
                'conditions'    => 'array',
                'delay_minutes' => 'integer|min:0',
                'active'        => 'boolean',
            ]);

            $autoEmail->update($data);
            return response()->json($autoEmail);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * Delete auto email
     */
    public function deleteAutoEmail(AutoEmail $autoEmail)
    {
        try {
            $autoEmail->delete();
            return response()->json(['message' => 'Auto email deleted successfully']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
