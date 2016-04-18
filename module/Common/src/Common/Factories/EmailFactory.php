<?php

/** 
 * ===============================================================
 * Copyright (C) 2016 - Peter Nagy.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ===============================================================
 * @author      Peter Nagy
 * @since       Jan 2016
 * @version     0.1
 * @description EmailFactory - Assebles email messages sent
 */

namespace Common\Factories;

use Zend\View\Model\ViewModel;

class EmailFactory{
    private static $mailConfig;
    
    private static function getConfig(){
        if(!self::$mailConfig){
            self::$mailConfig = \Common\Helper\SConfigLoader::getConfig('mailconfig');
        }
    }
    
    /**
     * Send a welcome email
     * 
     * @param string $emailAddress
     * @param string $recipientName
     * @param string $subject
     * @param array $templateVars - has to contain ['public_key', 'private_key', 'user_id']
     */
    public static function sendWelcomeEmail($emailAddress, $recipientName, $subject, array $templateVars){
        self::send($emailAddress, $recipientName, $subject, 'WelcomeTemplate', 'GeneralLayout', $templateVars);
    }
    
    /**
     * Prepare the resolver with a template
     *
     * @return Zend\View\Resolver\TemplateMapResolver
     */
    protected static function initResolver() {
        $resolver = new \Zend\View\Resolver\TemplateMapResolver();
        $resolver->setMap([
            'GeneralLayout' => __DIR__ . '/../../../view/layout/general-layout.phtml',
            'WelcomeTemplate' => __DIR__ . '/../../../view/layout/welcome-email.phtml'
        ]);

        return $resolver;
    }
    
    /**
     * Send email with template resolver
     * @param string $toAddress
     * @param string $toName
     * @param string $subject
     * @param string $templateName
     * @param string $layout
     * @param array $templateVars
     */
    protected static function send($toAddress, $toName, $subject, $templateName, $layout, $templateVars = []) {
        $view = new \Zend\View\Renderer\PhpRenderer;
        $view->setResolver(self::initResolver());

        $viewModel = new ViewModel;
        $viewModel->setTemplate($templateName)->setVariables($templateVars);
        $content = $view->render($viewModel);

        $viewLayout = new ViewModel;
        $viewLayout->setTemplate($layout)->setVariables(array(
            'content' => $content,
        ));

        $mail = self::getMailer();
        $mail->addAddress($toAddress, $toName);     // Add a recipient
        $mail->Subject = $subject;
        $mail->Body = $view->render($viewLayout);

        if (!$mail->send()) {
            \Common\Helper\SLogWriter::writeLog('email-log', sprintf('Subject: %s, Error: %s', $subject, $mail->ErrorInfo));
        }
    }
    
    /**
     * Setup PHPMailer
     * @return \PHPMailer
     */
    private static function getMailer() {
        self::getConfig();
        
        $mail = new \PHPMailer;
        $mail->XMailer = self::$mailConfig['xmailer'];
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = self::$mailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = self::$mailConfig['username'];
        $mail->Password = self::$mailConfig['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom(self::$mailConfig['from'], self::$mailConfig['sendername']);
        $mail->addReplyTo(self::$mailConfig['reply'], self::$mailConfig['sendername']);

        $mail->isHTML(true);
        return $mail;
    }
}