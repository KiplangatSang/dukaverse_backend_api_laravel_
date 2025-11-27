<?php
namespace App\Helpers\notifications;

class DukaverseNotification implements NotificationContract
{

    private $notification_type = "";
    private $notification_time = "18:00";
    private $message           = "New notification.";

    public function __construct($notification_type = null, $notification_time = null, $message = null)
    {
        if ($notification_type) {
            $this->notification_type = $notification_type;
        }

        if ($notification_time) {
            $this->notification_time = $notification_time;
        }

        if ($message) {
            $this->message = $message;
        }

    }

    public function notficationTime($notification_type)
    {
        $this->notification_type = $notification_type;
    }
    public function notificationType($notification_time)
    {
        $this->notification_time = $notification_time;
    }
    public function notificationMessage($message)
    {
        $this->message = $message;
    }
    public function getNotificationTime($transaction)
    {
        return $this->notification_type;
    }
    public function getNotificationType($transaction)
    {
        return $this->notification_time;
    }
    public function getNotificationMessage($transaction)
    {
        return $this->message;
    }
}
