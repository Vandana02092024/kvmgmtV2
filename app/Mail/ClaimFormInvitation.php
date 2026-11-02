<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ClaimFormInvitation extends Mailable
{
    public $invitationUrl;
    public $storeName;
    public $expiresAt;

    public function __construct($invitationUrl, $storeName, $expiresAt)
    {
        $this->invitationUrl = $invitationUrl;
        $this->storeName = $storeName;
        $this->expiresAt = $expiresAt;
    }

    public function build()
    {
        return $this->subject('Complete your claim form - ' . $this->storeName)
            ->view('emails.claim-form-invitation');
    }
}
