<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class mailFastpay extends Mailable
{
    use Queueable, SerializesModels;
    public $contenu;

    /**
     * Create a new message instance.
     */
  /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($contenu)
    {

        $this->contenu = $contenu;

    }


    public function build()
    {
        return $this->subject("WaaPay")
                    ->view('emails.emailfastpay');
    }
}
