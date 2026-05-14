<?php
namespace App\Model;

use Nette;
use App\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/** 
 * Mailer class for handling email sending functionality.
 */
class Mailer 
{
    /** @var Nette\Mail\IMailer */
    private $mailer;

    /** @var Model\Settings */
    private $settings;

    /** @var Model\SettingRecord */
    private $settingRecord;

    /** @var Model\Messages */
    private $messages;

    /** @var Model\LogMessages */
    private $logMessages;

    private static $accounts = [
        "default" => ["email" => "default@example.com", "password" => "ex123ample"],
        "custom"  => ["email" => "custom@example.com", "password" => "ex123ample"],
    ];

    /**
     * Mailer constructor.
     * 
     * @param Model\Settings $settings
     * @param Model\LogMessages $logMessages
     * @param Model\Messages $messages
     * @return void
     */
    public function __construct( Model\Settings $settings, Model\LogMessages $logMessages, Model\Messages $messages )
    {
        $this->settings = $settings;
        $this->settingRecord = $settings->find(1);
        $this->messages = $messages;
        $this->logMessages = $logMessages;
    }

    /**
     * Initialize the mailer with the specified account or default account.
     *
     * @param string|null $account
     * @return Nette\Mail\Message
     */
    private function initMailer($account = null)
    {
        if ($account === null) {
            $account = self::$accounts["default"];
        }

        $settingRecord = $this->settingRecord;

        $mail = $this->mailer;
        $mail = new Nette\Mail\Message;
        $mail->setFrom((string) $account["email"], $settingRecord->data()->name);
    
        return $mail;
    }

    /**
     * Send the email using the specified mail object, message ID, and account.
     *
     * @param Nette\Mail\Message $mail
     * @param int|null $messageId
     * @param string|null $account
     * @return void
     */
    private function send(Nette\Mail\Message $mail, $messageId = null, $account = null)
    {
        if ($account === null) {
            $account = self::$accounts["default"];
        }

        $values = [];
        $values["subject"] = $mail->getSubject();
        $values["body"] = $mail->getHtmlBody();

        $toRecipients = array_keys($mail->getHeader('To') ?? []);
        $ccRecipients = array_keys($mail->getHeader('Cc') ?? []); 
        $bccRecipients = array_keys($mail->getHeader('Bcc') ?? []);
        $allRec = array_unique(array_merge($toRecipients, $ccRecipients, $bccRecipients));

        $allRecipients = [];
        foreach ($allRec as $email) {
            $allRecipients[] = $email;
        }
        $values["address"] = implode(";", $allRecipients);
        $values["time"] = time();
        $values["message_id"] = $messageId;
        $this->logMessages->insert($values);

        $smtpMailer = new Nette\Mail\SmtpMailer([
            'host' => 'host.example.com',
            'username' => $account["email"],
            'password' => $account["password"],
            'secure' => 'tls',
            'port' => 587
        ]);

        $sendmailMailer = new Nette\Mail\SendmailMailer();
        $backupMailer = new PHPMailer();

        $mailer = new Nette\Mail\FallbackMailer([
            $smtpMailer,
            $sendmailMailer,
            $backupMailer
        ]);
        $mailer->send($mail);
    }

    /**
     * Get the HTML template for the email content.
     *
     * @param string $content
     * @return string
     */
    private function getMessageTemplate($content)
    {
        $settingRecord = $this->settingRecord;

        $html = '
            <!DOCTYPE html>
            <html lang="cs">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>' . $settingRecord->data()->name . '</title>
                <style>
                    /* Styling for the email content */
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        background-color: #fff;
                    }
                    .content, .header, .footer {
                        padding: 20px;
                    }
                    h1 {
                        color: #333;
                    }
                    p {
                        color: #666;
                    }
                    .header {
                        background-color: #ff8585;
                    }
                    .header img,
                    .footer img {
                        max-height: 40px;
                    }
                    .content {
                        background-color: #f8f8f8;
                    }
                    .footer {
                        background-color: #0c212e;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                <div class="header">
                    <a href="https://www.example.com/"><img src="/www/frontend/images/redesign/logo_mail.png"></a>
                </div>
                <div class="content">
                    ' . $content . '
                </div>
                <div class="footer">
                    <a href="https://www.example.com/"><img src="/www/frontend/images/redesign/logo_mail_footer.png"></a>
                </div>
                </div>
            </body>
            </html>
        ';
        return $html;
    }

    /**
     * Get the message subject for the specified message ID.
     *
     * @param int $messageId
     * @return string
     */
    private function getMessageSubject($messageId)
    {
        $result = "";
        $messageRecord = $this->messages->find($messageId);
        if ($messageRecord) {
            $result = $messageRecord->data()->subject;
        }
        return $result;
    }

    /**
     * Get the message body for the specified message ID.
     *
     * @param int $messageId
     * @return string
     */
    private function getMessageBody($messageId)
    {
        $result = "";
        $messageRecord = $this->messages->find($messageId);
        if ($messageRecord) {
            $result = $messageRecord->data()->body;
        }
        return $result;
    }

    /**
     * Send a message to a user with the specified message ID, email, and variables to replace in the message body.
     *
     * @param int $messageId
     * @param string $email
     * @param array $variables
     * @return void
     */
    public function sendMessageToUser($messageId, $email, $variables = [])
    {
        try {
            $mail = $this->initMailer();
            $mail->addTo($email);

            $content = $this->getMessageBody($messageId);

            foreach ($variables as $key => $value) {
                $content = str_replace("{{" . $key . "}}", $value, $content);
            }

            $mail->setSubject($this->getMessageSubject($messageId));
            $mail->setHtmlBody($this->getMessageTemplate($content));
            $this->send($mail, $messageId);
        } catch (Exception $e) {
        
        }
    }

    /**
     * Send a message to a list of recipients with the specified message ID, replacing variables in the message body.
     *
     * @param array $emailList
     * @param int $messageId
     * @return void
     */
    public function sendMessageTemplate($emailList, $messageId)
    {
        try {
            $mail = $this->initMailer();

            if (count($emailList)) {
                foreach ($emailList as $emailItem) {
                    $mail->addBCC(trim($emailItem));
                }

                $subject = $this->getMessageSubject($messageId);
                $body = $this->getMessageBody($messageId);

                $content = "<h2>" . $subject . "</h2>";
                $content .= $body;

                $mail->setSubject($subject);
                $mail->setHtmlBody($this->getMessageTemplate($content));
                $this->send($mail, $messageId);
            }
        
        } catch (Exception $e) {

        }
    }

    /**
     * Send a custom message to a list of recipients with the specified subject and body, using a custom template.
     *
     * @param array $emailList
     * @param string $subject
     * @param string $body
     * @return void
     */
    public function sendMessageCustomTemplate($emailList, $subject, $body)
    {
        try {
            $mail = $this->initMailer();

            if (count($emailList)) {
                foreach ($emailList as $emailItem) {
                    $mail->addBCC(trim($emailItem));
                }

                $content = "<h2>" . $subject . "</h2>";
                $content .= $body;

                $mail->setSubject($subject);
                $mail->setHtmlBody($this->getMessageTemplate($content));
                $this->send($mail);
            }
        } catch (Exception $e) {
        
        }
    }
}