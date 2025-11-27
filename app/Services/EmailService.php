<?php

namespace App\Services;

use App\Models\EmailConfig;
use App\Models\EmailNotification;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

// use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService extends BaseService
{
    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }
    /**
     * Read emails from IMAP for a given config
     */
    public function readEmails(EmailConfig $config)
    {
        try {
            $client = \Webklex\IMAP\Facades\Client::make([
                'host' => $config->imap_host,
                'port' => $config->imap_port,
                'encryption' => $config->imap_encryption,
                'username' => $config->imap_username,
                'password' => $config->imap_password,
            ]);

            $inbox = $client->getFolder('INBOX');
            $messages = $inbox->messages()->unseen()->get();

            foreach ($messages as $message) {
                // Check if already processed
                if (EmailNotification::where('message_id', $message->getMessageId())->exists()) {
                    continue;
                }

                $notification = EmailNotification::create([
                    'email_config_id' => $config->id,
                    'message_id' => $message->getMessageId(),
                    'subject' => $message->getSubject(),
                    'body' => $message->getTextBody() ?: $message->getHTMLBody(),
                    'from_email' => $message->getFrom()->first()->mail,
                    'from_name' => $message->getFrom()->first()->personal,
                    'to' => $message->getTo()->map(fn($to) => $to->mail)->toArray(),
                    'attachments' => $this->handleAttachments($message),
                    'received_at' => $message->getDate(),
                ]);

                // Store in Firestore
                $this->storeInFirestore($notification);
            }

            $client->disconnect();
        } catch (\Exception $e) {
            Log::error('IMAP Error: ' . $e->getMessage());
        }
    }

    /**
     * Send email using SMTP config
     */
    public function sendEmail(array $data, EmailConfig $config)
    {
        // Configure mail dynamically
        config([
            'mail.mailers.smtp.host' => $config->smtp_host,
            'mail.mailers.smtp.port' => $config->smtp_port,
            'mail.mailers.smtp.encryption' => $config->smtp_encryption,
            'mail.mailers.smtp.username' => $config->smtp_username,
            'mail.mailers.smtp.password' => $config->smtp_password,
            'mail.from.address' => $config->from_email,
            'mail.from.name' => $config->from_name,
        ]);

        Mail::mailer('smtp')->raw($data['body'], function ($message) use ($data, $config) {
            $message->to($data['to'])
                    ->subject($data['subject'])
                    ->from($config->from_email, $config->from_name);
        });
    }

    /**
     * Store notification in Firestore
     */
    private function storeInFirestore(EmailNotification $notification)
    {
        $firestore = app('firebase.firestore');
        $collection = $firestore->collection('email_notifications');
        $collection->add([
            'id' => $notification->id,
            'subject' => $notification->subject,
            'body' => $notification->body,
            'from_email' => $notification->from_email,
            'received_at' => $notification->received_at->toISOString(),
            'processed' => $notification->processed,
        ]);
    }

    /**
     * Handle email attachments
     */
    private function handleAttachments($message)
    {
        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            // Save attachment to storage or handle as needed
            $path = $attachment->save(storage_path('app/attachments'));
            $attachments[] = [
                'name' => $attachment->getName(),
                'path' => $path,
            ];
        }
        return $attachments;
    }
}
