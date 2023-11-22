<?php

/**
 * @author Christoph Wurst <christoph@owncloud.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *812159
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactor_Email\Provider; 

use OCA\TwoFactor_Email\Service\Email as EmailService;
use OCA\TwoFactor_Email\Service\ExcludedGroupService;
use OCA\TwoFactor_Email\Service\TwoFactorOTP ;


use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IUser;
use OCP\Template;
use OCP\ISession;

class TwoFactorEmailProvider implements IProvider {
	

	/** @var ISession */
	protected $session;


	/** @var EmailService */
	public $emailService;
	
	/** @var TwoFactorOTP */
	public $twoFactorOTP;
	
	/** @var ExcludedGroupService */
	public $excludedGroupService;
	/**
	 * Get unique identifier of this 2FA provider
	 *
	 * @return string
	 */
	
	 public function __construct(
	 ISession $session ,
	 EmailService $emailService, 
	 TwoFactorOTP $twoFactorOTP,
	 ExcludedGroupService $excludedGroupService
	 )
	 {
		$this->emailService = $emailService;
		$this->session = $session;
		$this->twoFactorOTP = $twoFactorOTP;  
		$this->excludedGroupService = $excludedGroupService;

		$this->excludedGroupService->createGroupExcluded("NON2FA");
	}


	private function getSessionKey(): string {
		return 'twofactor_email_secret';
	}
		/**
	 * Get unique identifier of this 2FA provider
	 *
	 * @return string
	 */
	public function getId() {
		return 'email';
	}

	/**
	 * Get the display name for selecting the 2FA provider
	 *
	 * @return string
	 */
	public function getDisplayName() {
		// TODO: L10N
		return 'Email';
	}

	/**
	 * Get the description for selecting the 2FA provider
	 *
	 * @return string
	 */
	public function getDescription() {
		// TODO: L10N
		return 'Get a token via e-mail';
	}

	private function storeSession($secret){
		$this->session->set($this->getSessionKey(), [
			"secret"=>$secret,
			"created" => time()
	]);
	}
	private function getSecret(): string {
		if ($this->session->exists($this->getSessionKey())) {
			return $this->session->get($this->getSessionKey());
		}

		$pubkey = $this->twoFactorOTP->getPubKey();
		$secret = $this->twoFactorOTP->getOtp($pubkey);
		$this->storeSession($secret);

		return $secret;
	}
	/**
	 * Get the template for rending the 2FA provider view
	 *
	 * @param IUser $user
	 * @return Template
	 */
	public function getTemplate(IUser $user) {
		 
		$this->session->remove($this->getSessionKey());
		
		$secret = $this->getSecret();


		if(( $user->getEMailAddress() === NULL ) ||  (trim( $user->getEMailAddress() ) == "") ) {
			return new Template('twofactor_email', 'emailnotfound');			
		}
		
		try {
			$this->emailService->send($user, $secret);
		} catch (\Exception $ex) {
			return new Template('twofactor_email', 'error');
		}


		$template = new Template('twofactor_email', 'challenge');
		$template->assign('emailAddress', $user->getEMailAddress());
		return $template ;
	}

	/**
	 * Verify the given challenge
	 *
	 * @param IUser $user
	 * @param string $challenge
	 */
	public function verifyChallenge(IUser $user, $challenge) {

		$secret = $this->session->get($this->getSessionKey());
		$timestamp = time() - $secret["created"]; 

		$this->session->remove($this->getSessionKey());
		
		
		if ((intval($challenge) !=  $secret['secret']) || $timestamp > 100) { 
			return false;
		} 

		return true;
	}
	
	/**
	 * Decides whether 2FA is enabled for the given user
	 *
	 * @param IUser $user
	 * @return boolean
	 */
	public function isTwoFactorAuthEnabledForUser(IUser $user) {
		return $this->excludedGroupService->userIncluded($user);
	}

}
