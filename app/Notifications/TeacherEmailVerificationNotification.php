<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Models\teacher_account_seeders;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;


class TeacherEmailVerificationNotification extends Notification
{
    use Queueable;
    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    private $otp;
    /**
     * Create a new notification instance.
     */
    public function __construct()
    {

        $this->subject = 'Verification needed';
        $this->fromEmail = 'Akrem@gmail.com';
        $this->mailer = 'smtp';
        $this->otp = new Otp;
    }


    public function via($notifiable): array
    {
        return ['mail'];
    }


    public function toMail($notifiable)
    {


        // dd($notifiable->idDoctor);
        $teacher = Student::find(426) ;

        $email = $teacher->email;

        $firstName = $teacher->name;
 



        $otp = $this->otp->generate($email, 6, 10);     // 6 digits token and 10 min to expired the token
        return (new MailMessage)
        ->mailer($this->mailer )
        ->subject($this->subject)
        ->greeting('hello ' . $firstName)
        ->line($this->message)
        ->line('code: ' . $otp->token);
    }


    public function toArray(object $notifiable): array
    {
        return [

        ];
    }
}
