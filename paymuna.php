<?php // -->

if(!defined('_PS_VERSION_')) {
	exit;
}

/**
 * PayMuna Prestashop Extension.
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 *
 * @vendor 	Openovate Labs
 * @author 	Charles Zamora <czamora@openovate.com>
 */
class PayMuna extends PaymentModule {
	/**
	 * Set the default module parameters.
	 *
	 * @return this
	 */
	public function __construct()
	{
		// set the module name
		$this->name 					= 'paymuna';
		// set where this module will appear on module tabs
		$this->tab 						= 'checkout';
		// set module version
		$this->version 					= '1.0.0';
		// set module author
		$this->author 					= 'Openovate Labs';
		// set whether we load this module on module list
		$this->need_instance 			= 0;
		// set if package is configurable
		$this->is_configurable 			= 1;
		// set version compliance flag
		$this->ps_versions_compliancy 	= array('min' => '1.6', 'max' => _PS_VERSION_);
		// set that we are prestashop bootstrap tools
		$this->bootstrap 				= true;
		// set our i18n support
		$this->limited_countries 		= 'en';
		// set module controllers
		$this->controllers 				= array('confirmation');

		// call out the default construct
		parent::__construct();

		// set the module display name
		$this->displayName = $this->l('PayMuna');
		// set the module display description
		$this->description = $this->l('Checkout platform for every e-commerce site. Easy payments, easy setup.');

		// set confirm unsinstall message
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		// check if paymuna configuration is set
		if(!Configuration::get('PAYMUNA')) {
			// set the warning if configuration is not set
			$this->warning = $this->l('No name provided.');
		}
	}

	/**
	 * Default installation process.
	 *
	 * @return 	bool
	 */
	public function install()
	{
		// check if multistore option is enabled,
		// if enabled, set the current context to
		// all shops on this installation of Prestashop.
		if(Shop::isFeatureActive()) {
			Shop::setContext(Shop::CONTEXT_ALL);
		}

		// parent class installed?
		if(!parent::install()
		|| !$this->registerHook('paymentReturn')) {
			return false;
		}

		return true;
	}

	/**
	 * Default un-installation process.
	 *
	 * @return 	bool
	 */
	public function uninstall()
	{
		// parent class installed? or
		// module configuration set?
		if(!parent::uninstall()
		|| !Configuration::deleteByName('PAYMUNA')) {
			return false;
		}

		return true;
	}

	/**
	 * Default function to be called on
	 * configuration page load.
	 *
	 * @return 	string
	 */
	public function getContent()
	{
		// the default output placeholder
		$output = null;

		$error = false;

		// on form submit, do the action
		if(Tools::isSubmit('submit' . $this->name)) {
			// get the configuration data
			$configuration = array(
				// get the api token
				'PAYMUNA_API_TOKEN'  => Tools::getValue('checkout_token'),
				// get the api secret
				'PAYMUNA_API_SECRET' => Tools::getValue('checkout_secret'),
				// get the module environment
				'PAYMUNA_ENVIRONMENT' => Tools::getValue('checkout_environment'),
				// get the reference id
				'PAYMUNA_REFERENCE' => Tools::getValue('checkout_session'),
			);

			// validate if data is empty
			if(!$configuration || empty($configuration)) {
				// set the page error
				$output .= $this->displayError($this->l('Invalid API Configuration value!'));
			} else {
				if(!$this->validForm($configuration)) {
					$error = true;

					// assign history
					$this->smarty->assign('config', $configuration);

					// set the page error
					$output .= $this->displayError($this->l('Invalid API Configuration value, please fill up required fields.'));
				} else {
					// update configuration
					Configuration::updateValue('PAYMUNA', serialize($configuration));

					// display success message
					$output .= $this->displayConfirmation($this->l('API Configuration successfully updated!'));
				}
			}
		}

		if(!$error) {
			// get the configuration
			$this->smarty->assign('config', unserialize(Configuration::get('PAYMUNA')));
		}

		// return $output . $this->displayForm();
		return $output . $this->display(__FILE__, 'views/templates/admin/paymuna.tpl');
	}

	public function validForm($configuration)
	{
		if(!isset($configuration['PAYMUNA_API_TOKEN'])
		|| !isset($configuration['PAYMUNA_API_SECRET'])
		|| !isset($configuration['PAYMUNA_ENVIRONMENT'])
		|| !isset($configuration['PAYMUNA_REFERENCE'])
		) {
			return false;
		}

		if(empty($configuration['PAYMUNA_API_TOKEN'])
		|| empty($configuration['PAYMUNA_API_SECRET'])
		|| empty($configuration['PAYMUNA_ENVIRONMENT'])
		|| empty($configuration['PAYMUNA_REFERENCE'])
		) {
			return false;
		}

		return true;
	}

	public function hookPaymentReturn($params)
	{
		// format total to pay
		$params['total_to_pay'] = number_format($params['total_to_pay'], 2, '.', ',');

		// assign the paramters
		$this->smarty->assign($params);
		// well, the payment is always ok, error will occur
		// on paymuna checkout page
		$this->smarty->assign('status', 'ok');

		return $this->display(__FILE__, 'payment_return.tpl');
	}
}
