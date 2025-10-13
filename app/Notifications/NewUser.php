<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\User;

class NewUser extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $password;
    private $emailSetting;
    private $clientSignup;
    private $signup;

    public function __construct(User $user, $password, $clientSignup = false, $signup = false)
    {
        $this->password = $password;
        $this->clientSignup = $clientSignup;
        $this->signup = $signup;


        // When there is company of user.
        if ($this->company) {
            $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'user-registrationadded-by-admin')->first();
        }
    }

    /**
     * Get the notification's delivery channels.
     *t('mail::layout')
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];

        if (is_null($this->company)) {
            array_push($via, 'mail');

            return $via;
        }

        if ($this->emailSetting->send_email == 'yes' && ($notifiable->email_notifications == '' || $notifiable->email_notifications) && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newUser.subject'), $notifiable->name);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {


        $url = route('login');
        $url = getDomainSpecificUrl($url, $this->company);

        // WORKSUITESAAS
        $this->password = $this->password ?: __('superadmin.previousPassword');

        if ($this->clientSignup == true) {
            $content = __('email.newUser.clientSignupMessage') . '<br>';
        } else {
            $content = __('email.newUser.text') . '<br><br>' . __('app.email') . ': <b>' . $notifiable->email . '</b><br>' . __('app.password') . ': <b>' . $this->password . '</b>';
        }

        if ($this->signup) {
            $this->company = null;
        }

        $build = parent::build($notifiable);
        $build
            ->subject(__('email.newUser.subject') . ' ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company ? $this->company->header_color : null,
                'actionText' => __('email.newUser.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function toArray($notifiable)
    {
        return $notifiable->toArray();
    }
}
