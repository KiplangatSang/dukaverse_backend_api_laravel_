<?php

namespace App\Notifications;

use App\Helpers\Sms\SmsMessage;
use App\Repositories\OrdersRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $order = null;
    public function __construct($order)
    {
        //
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];

    }

    // public function via($notifiable)
    // {
    //     return [VoiceChannel::class];
    // }

    /**
     * Get the voice representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return VoiceMessage
     */
    // public function toVoice($notifiable)
    // {
    //     // ...

    //     // dd( $message);
    //     $basic  = new \Nexmo\Client\Credentials\Basic(env('VONAGE_KEY'), env('VONAGE_SECRET'));

    //     $client = new \Nexmo\Client($basic);

    //     try {
    //         $message = $client->message()->send([
    //             'to' => "254714680763",
    //             'from' =>  "DukaVerse",
    //             'text' =>  "You have placed an order id " . $this->order->orderId . " " . $this->order->items_count . " items " . " cost " . $this->order->actual_cost . " at " . $this->order->created_at->format('d m Y H:i'),
    //         ]);
    //     } catch (Exception $e) {
    //         return $e->getMessage();
    //     }

    //     return true;
    //     // return (new SmsMessage)
    //     //     ->to('254714680763')
    //     //     ->line("You have placed an order " . json_encode($this->order));;
    // }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $ordersrepo = new OrdersRepository($this->order->orderable);

        $ordersdata = $ordersrepo->showOrderData($this->order->id);
        return (new MailMessage)
            ->view('emails.orderplaced', compact('ordersdata'));
    }

    public function toDatabase($notifiable)
    {
        return [
            'link' => "/client/orders/show/" . $this->order->id,
            'message' => "New order has been added",
            'data' => json_encode($this->order),
        ];
    }

    // public function toSms($notifiable)
    // {
    //     return (new VonageMessage)
    //         ->content('Success New order has been added. '.json_encode($this->order)." Make payment as soon as posible")
    //         ->from('Dukaverse ');
    // }

    public function toVonage($notifiable)
    {
        // We are assuming we are notifying a user or a model that has a telephone attribute/field.
        // And the telephone number is correctly formatted.
        // TODO: SmsMessage, doesn't exist yet :-) We should create it.
        return (new SmsMessage)
            ->to($notifiable->phoneno)
            ->line("You have placed an order " . json_encode($this->order));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
