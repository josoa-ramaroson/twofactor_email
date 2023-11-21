<?php

declare(strict_types=1);

namespace OCA\TwoFactor_Email\Service;

use OCP\Defaults;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IUser;
use OCP\Mail\IMailer;

class Email {
	/** @var IMailer */
	private $mailer;

	/** @var IL10N */
	private $l10n;

	/** @var ILogger */
	private $logger;

	/** @var Defaults */
	private $themingDefaults;

	public function __construct(IMailer $mailer,
								IL10N $l10n,
								ILogger $logger,
								Defaults $themingDefaults) {
		$this->mailer = $mailer;
		$this->l10n = $l10n;
		$this->logger = $logger;
		$this->themingDefaults = $themingDefaults;
	}

	/**
	 * @param IUser $user
	 * @param string $authenticationCode
	 */
	public function send(IUser $user, string $authenticationCode): void {
		$this->logger->debug('sending email message to ' . $user->getEMailAddress() . ', code: $authenticationCode');

		

		$message = $this->mailer->createMessage();
		$message->setTo([ $user->getEMailAddress() => $user->getDisplayName() ]);
		$message->setSubject("Authentication mail");

		// setting its body 
		// git it its full path 
		$msgHeader = file_get_contents("/home/jojoso/Desktop/owncloud_app/owncloud/apps/twofactor_email/lib/Service/email/header.html");
		$msgBody = file_get_contents("/home/jojoso/Desktop/owncloud_app/owncloud/apps/twofactor_email/lib/Service/email/body.html");
		$msgFooter = file_get_contents("/home/jojoso/Desktop/owncloud_app/owncloud/apps/twofactor_email/lib/Service/email/footer.html");
		$msgBody = sprintf($msgBody,$user->getUserName(),$authenticationCode);
		$htmlMsg = $msgHeader.$msgBody.$msgFooter;
		 
		$message->setHtmlBody($htmlMsg);
		
		
	
		$this->mailer->send($message);
	}
}
