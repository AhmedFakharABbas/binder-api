<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class UserCreated extends Notification
{
    use Queueable;

    public  $data;
    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data,$token)
    {
       $this->data=$data;
        $this->token=$token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from($address = 'noreply@domain.com', $name = 'Binder Group')
            ->subject('Binder Group Web Access')
           ->greeting('Hi '.$this->data->first_name.' '.$this->data->last_name)
            ->line('Welcome to Binder Group Web service.We are pleased to inform you that your Binder Group Web account has been activated.')
            ->line('You can access your Account using the following Sign-in details.')
//            ->line(new HtmlString('User Name : <strong>'.$this->data->email.'</strong>  '.'Password : <strong>'.$this->data->password.'</strong>'))

            ->action('Set password', url('http://localhost:4200/auth/reset-password/'.$this->token.'?email='.$this->data->email))
//            ->action('Sign in', url('https://binder.technocares.com/auth/signin'))
//            ->action($this->data['text'],$this->data['url'])
       //     ->action(trans('messages.notification.new_account.action'),url(config('app.ui').'/reset-password/'.$this->token.'?email='.$notifiable->email))
            ->line('Thank you for using our application!');

//
    }

//return (new MailMessage) . :.
//-> from('support@eiight.app',$this-> project_name)
//->subject('Sale Offer-'.$this-> project_name)
//->markdown('vendor.notifications.email',['body_image'=> $body_image,
//'header_image' => $header_image,'username'=> $this->username, 'user_phone' => $this-> user_phone])
//->greeting('Hi '.$this->customer_name)
//->line('Thank you for your interest in '.$this->project_name.'. Please find the sale offer attached.')
//->attach(storage_path('app/public/pdf/'.$this->path), [
//'as' => $this->filename,
//'mime' => 'application/pdf',
//]);

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
