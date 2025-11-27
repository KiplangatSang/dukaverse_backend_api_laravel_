<?php
namespace App\Helpers\notifications;

interface NotificationContract
{

    public function notficationTime($time);
    public function notificationType($amount);
    public function notificationMessage($amount);
    public function getNotificationTime($transaction);
    public function getNotificationType($transaction);
    public function getNotificationMessage($transaction);
}
