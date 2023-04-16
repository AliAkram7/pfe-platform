<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class SendEmailTeacher extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($teacherName, $teacherCode,$teacherEmail, $teacherPassword)
    {
        $this->teacherName = $teacherName;
        $this->teacherCode = $teacherCode;
        $this->teacherEmail = $teacherEmail;
        $this->teacherPassword = $teacherPassword;
    }

    public function build()
    {
        return $this->view('emailTeacher')
            ->with([
                'teacherName' => $this->teacherName,
                'teacherCode' => $this->teacherCode,
                'teacherEmail' => $this->teacherEmail,
                'teacherPassword' => $this->teacherPassword,
            ]);
    }
}
