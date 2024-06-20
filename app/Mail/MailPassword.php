<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Helpers\EnvironmentHelper;

class MailPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public $footer;

    public $privacy;

    public $copyright;

    public $appimg;

    public $logo;

    public $previous;

    public $color;

    protected $supportPhone;

    protected $supportMail;

    protected $company;

    protected $app;

    protected $name;

    protected $hash;

    protected $lang;

    protected $fromAddress;

    protected $fromName;

    protected $assets;

    protected $template;

    public function __construct($lang, $name, $hash, $template)
    {
        $this->lang = $lang;
        $this->template = $template;
        $this->company = env('MAIL_COMPANY');
        $this->fromAddress = env('MAIL_FROM_ADDRESS');
        $this->fromName = env('MAIL_FROM_NAME');
        $this->supportPhone = '+19563224511';
        $this->supportMail = 'help@grainchain.io';
        $this->name = $name;
        $this->hash = $hash;
        $this->priority(1);
        $this->app = "SiloSys";
        $this->assets = array(
            "RESET_PASSWORD" => array(
                "subject" => "messages.email.reset_password_subject",
                "body" => "messages.email.reset_password_body",
                "footer" => "messages.email.reset_password_footer",
                "privacy" => "messages.email.privacy",
                "copyright" => "messages.email.copyright",
                "previous" => "messages.email.reset_password_previous",
                "appimg" => "assets/img/pw.png",
                "logo" => "messages.email.reset_password_logo",
                "color" => "#0068ff",
                "view" => "emails.resetpwd"
            ),
            "NEW_USER_WEB" => array(
                "subject" => "messages.email.new_user_subject",
                "body" => "messages.email.new_user_body",
                "footer" => "messages.email.new_user_footer",
                "privacy" => "messages.email.privacy",
                "copyright" => "messages.email.copyright",
                "previous" => "messages.email.new_user_previous",
                "appimg" => "messages.email.new_user_logo_web",
                "logo" => "messages.email.new_user_logo_web",
                "color" => "#0068ff",
                "view" => "emails.newuser"
            ),
            "NEW_USER_APP" => array(
                "subject" => "messages.email.new_user_subject",
                "body" => "messages.email.new_user_body",
                "footer" => "messages.email.new_user_footer",
                "privacy" => "messages.email.privacy",
                "copyright" => "messages.email.copyright",
                "previous" => "messages.email.new_user_previous",
                "appimg" => "messages.email.new_user_logo_mobile",
                "logo" => "messages.email.new_user_logo_mobile",
                "color" => "#42b62e",
                "view" => "emails.newuser"
            ),
        );
    }

    public function build()
    {
        App::setLocale($this->lang);
        $this->appimg = EnvironmentHelper::getAssetPath($this->getKey("appimg"));
        $this->logo = EnvironmentHelper::getAssetPath(__($this->getKey("logo")));
        $this->color = $this->getKey("color");
        $subject = $this->company . " - " . __($this->getKey("subject"));
        Log::debug($subject);

        $this->body = __($this->getKey("body"), [
            "name" => $this->name,
            "app" => $this->app,
            "hash" => $this->hash,
            "color" => $this->color
        ]);
        Log::debug($this->body);

        $this->footer = __($this->getKey("footer"), [
            "mailto" => $this->supportMail,
            "phone" => $this->supportPhone
        ]);
        Log::debug($this->footer);

        $this->privacy = __($this->getKey("privacy"), [
            "year" => Date('Y'),
            "app" => $this->app
        ]);
        Log::debug($this->privacy);

        $this->copyright = __($this->getKey("copyright"), ["app" => $this->app]);
        Log::debug($this->copyright);

        $this->previous = __($this->getKey("previous"), ["app" => $this->app]);
        Log::debug($this->previous);

        Log::debug($this->fromAddress);
        Log::debug($this->fromName);
        return $this->from($this->fromAddress, $this->fromName)
            ->subject($subject)
            ->view($this->getKey("view"));
    }

    public function failed(\Exception $exception)
    {
        Log::error($exception);
    }


    private function getKey($prop)
    {
        return $this->assets[$this->template][$prop];
    }
}