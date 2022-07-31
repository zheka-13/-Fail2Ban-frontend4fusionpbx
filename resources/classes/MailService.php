<?php

class MailService
{

    /**
     * @var array
     */
    private $emails;

    /**
     * @var array
     */
    private $text;

    public function __construct($emails)
    {
        $this->emails = $emails;
        $language = new text;
        $this->text = $language->get();
    }

    public function addedToBlacklist($ip)
    {
        $subject = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-added-blacklist'];
        $message = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-added-blacklist']." ".$this->text['fail2ban-by-user']." ".$_SESSION['user']['username'].". ".$this->text['fail2ban-action-time'].": ".date("d.m.Y H:i:s");
        $this->sendEmails($subject, $message);
    }
    public function removedFromBlacklist($ip)
    {
        $subject = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-removed-blacklist'];
        $message = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-removed-blacklist']." ".$this->text['fail2ban-by-user']." ".$_SESSION['user']['username'].". ".$this->text['fail2ban-action-time'].": ".date("d.m.Y H:i:s");
        $this->sendEmails($subject, $message);
    }
    public function addedToWhitelist($ip)
    {
        $subject = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-whitelisted'];
        $message = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-whitelisted']." ".$this->text['fail2ban-by-user']." ".$_SESSION['user']['username'].". ".$this->text['fail2ban-action-time'].": ".date("d.m.Y H:i:s");
        $this->sendEmails($subject, $message);
    }
    public function removedFromWhitelist($ip)
    {
        $subject = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-whitelist-removed'];
        $message = $this->text['fail2ban-ip']." ".$ip." ".$this->text['fail2ban-whitelist-removed']." ".$this->text['fail2ban-by-user']." ".$_SESSION['user']['username'].". ".$this->text['fail2ban-action-time'].": ".date("d.m.Y H:i:s");
        $this->sendEmails($subject, $message);
    }



    /**
     * @param $subject
     * @param $body
     * @return void
     */
    private function sendEmails($subject, $body)
    {
        foreach ($this->emails as $email)
        {
            mail($email, $subject, $body);
        }
    }
}