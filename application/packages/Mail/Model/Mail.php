<?php

namespace Mail\Model;

use Zend\Mime;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Mail
{
    protected $attachments = array();

    public function addAttachment($attachment)
    {
        if ($attachment instanceof Mime\Part) {
            $this->attachments[] = $attachment;
        }

        if (is_string($attachment) && file_exists($attachment)) {
            $pathinfo        = pathinfo($attachment);
            $at              = new Mime\Part(file_get_contents($attachment));
            $at->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    = Mime\Mime::ENCODING_BASE64;
            $at->filename    = $pathinfo['basename'];
            $this->attachments[] = $at;
        }
    }

    public function send($subject, $text, $to, $from = null)
    {
        if (!config('mail.enabled')) {
            return;
        }

        $message = new Message();
        $message->setSubject($subject);

        if ($from) {
            if (is_array($from)) {
                $message->setFrom($from[0], $from[1]);
            } else {
                $message->setFrom($from);
            }
        }

        $message->addTo($to);

        $content = '<html><head><title>' . $subject . '</title></head><body>' . $text . '</body></html>';

        $html = new Mime\Part($content);
        $html->setType(Mime\Mime::TYPE_HTML);
        $html->setCharset('utf-8');

        $mimeMessage = new Mime\Message();
        $mimeMessage->addPart($html);

        foreach ($this->attachments as $attachment) {
            $mimeMessage->addPart($attachment);
        }

        $message->setBody($mimeMessage);

        try {
            $transport = new SmtpTransport(
                new SmtpOptions(config('mail.options'))
            );
            $transport->send($message);
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }
}