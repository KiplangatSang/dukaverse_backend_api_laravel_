<?php
namespace App\Casts;

use App\Helpers\notifications\DukaverseNotification;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DukaverseNotificationCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return DukaverseNotification
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $data = json_decode($value, true);
        if (empty($data)) {
            return new DukaverseNotification();
        }
        return new DukaverseNotification(
            $data['notification_type'] ?? null,
            $data['notification_time'] ?? null,
            $data['message'] ?? null
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof DukaverseNotification) {
            return json_encode([
                'notification_type' => $value->getNotificationType(null),
                'notification_time' => $value->getNotificationTime(null),
                'message'           => $value->getNotificationMessage(null),
            ]);
        }
        return $value;
    }
}
