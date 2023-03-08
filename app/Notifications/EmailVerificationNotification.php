<?php

namespace App\Notifications;

use App\Models\Student;
use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class EmailVerificationNotification extends Notification
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
        $this->message = "Cilck the link below: ";
        $this->subject = 'Verivication needed';
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
        $student = Student::find($notifiable->id);
        $email = $student->email;
        $firstName = $student->name;
        $otp = $this->otp->generate($email, 6, 10); // 6 digits token and 10 min to expired the token
        return (new MailMessage)
        ->mailer($this->mailer )
        ->subject($this->subject)
        ->greeting('Hello' . $firstName)
        ->line($this->message)
        ->line('code: ' . $otp->token);
    }


    public function toArray(object $notifiable): array
    {
        return [
            
        ];
    }
}
