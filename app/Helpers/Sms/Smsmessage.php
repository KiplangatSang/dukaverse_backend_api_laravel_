<?php


namespace App\Helpers\Sms;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SmsMessage
{

    protected string $user;
    protected string $password;
    protected string $to;
    protected string $from;
    protected array $lines;
    protected string $dryrun = 'no';

    /**
     * SmsMessage constructor.
     * @param array $lines
     */
    public function __construct($lines = [])
    {
        $this->lines = $lines;

        // Pull in config from the config/services.php file.
        $this->from = config('services.vonage.from');
        // $this->baseUrl = config('services.vonage.base_url');
        $this->user = config('services.vonage.key');
        $this->password = config('services.vonage.secret');
    }

    public function line($line = ''): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function to($to): self
    {
        $this->to = $to;

        return $this;
    }

    public function from($from): self
    {
        $this->from = $from;

        return $this;
    }

    public function send(): mixed
    {
        if (!$this->from || !$this->to || !count($this->lines)) {
            throw new \Exception('SMS not correct.');
        }
        $this->sendNexmoSmsNotificaition($this->to, $this->from, "Hello");

        return true;
        // return Http::baseUrl($this->baseUrl)->withBasicAuth($this->user, $this->pass)
        //     ->asForm()
        //     ->post('sms', [
        //         'from' => $this->from,
        //         'to' => $this->to,
        //         'message' => $this->lines->join("\n", ""),
        //         'dryryn' => $this->dryrun
        //     ]);
    }

    public function dryrun($dry = 'yes'): self
    {
        $this->dryrun = $dry;

        return $this;
    }

    public function sendNexmoSmsNotificaition(
        $to,
        $from,
        $text
    ) {
        $basic  = new \Nexmo\Client\Credentials\Basic(env('VONAGE_KEY'), env('VONAGE_SECRET'));

        $client = new \Nexmo\Client($basic);

        try {
            $message = $client->message()->send([
                'to' => $to,
                'from' =>  $from,
                'text' =>  $text,
            ]);

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }
}
