<?php
/**
 * Olark chat integration module for Interspire Shopping Cart.
 */
class LIVECHAT_OLARK extends ISC_LIVECHAT
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->SetName(GetLang('OlarkName'));
		$this->SetImage('logo.png');
	}

	/**
	 * Define the configurable settings for the Olark integration.
	 */
	public function SetCustomVars()
	{
		if(!$this->GetValue('chatcode')){
			$fieldsVisible = false;
			$this->SetHelpText(GetLang('OlarkHelp'));
		} else {
			$fieldsVisible = true;
			$this->SetHelpText(GetLang('OlarkHelpIntegrated'), 'info');
		}
		$this->_variables['action'] = array(
			'name' => 'Olark Account',
			'type' => 'custom',
			'callback' => 'ShowRegistrationFields'
		);

		$this->_variables['chatcode'] = array(
			'name' => GetLang('OlarkChatCode'),
			'type' => 'textarea',
			'help' => GetLang('OlarkChatCodeHelp'),
			'default' => '',
			'required' => true,
			'visible' => $fieldsVisible
		);

	}

	/**
	 * Get the live chat tracking code for this module for the specified page position.
	 *
	 * @return string String containing the live chat code.
	 */
	public function GetLiveChatCode()
	{
		return $this->GetValue('chatcode');
	}

	/**
	 * Show the form to register/use an existing Olark account.
	 */
	public function ShowRegistrationFields()
	{
		return $this->ParseTemplate('olark_registration', true);
	}

}
