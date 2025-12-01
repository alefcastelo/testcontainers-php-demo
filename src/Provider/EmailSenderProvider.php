<?php

namespace App\Provider;

class EmailSenderProvider
{
    public function __construct(
    ) {
    }

    public function sendEmail(string $email, string $subject, string $body): void
    {
        echo "Sending email to $email with subject $subject and body $body";
    }
}
