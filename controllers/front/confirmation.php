<?php // -->

include dirname(__FILE__) . '/../../lib/PayMunaBase.php';

/**
 * PayMuna Prestashop Extension.
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 *
 * @vendor 	Openovate Labs
 * @author 	Charles Zamora <czamora@openovate.com>
 */
class PayMunaConfirmationModuleFrontController extends ModuleFrontController {
	/**
	 * Post process functionalities.
	 *
	 * @return 	this
	 */
	public function postProcess()
	{
		// get session id
		$session  = Tools::getValue('session');

		// validate required params
		if (!isset($_GET['success'])
			|| !isset($_GET['reference'])) {

			header('HTTP/1.0 400 Bad Request');
			die('result=FAIL; Please contact admin regarding Paymuna process');
		}

		// get reference
		$reference = $_GET['reference'];

		// get paymuna configuration
		$configuration = unserialize(Configuration::get('PAYMUNA'));

		// redirect if configuration is not valid
		if(!isset(
			$configuration['PAYMUNA_API_TOKEN'],
			$configuration['PAYMUNA_API_SECRET'],
			$configuration['PAYMUNA_ENVIRONMENT'],
			$configuration['PAYMUNA_REFERENCE'])
		) {
			Tools::redirect('/');
		}

		$token  = $configuration['PAYMUNA_API_TOKEN'];
		$secret = $configuration['PAYMUNA_API_SECRET'];
		$env    = $configuration['PAYMUNA_ENVIRONMENT'];

		// initialize base class
		$paymuna = new PayMunaBase($token, $secret, $env);

		// get checkout details
		$details = $paymuna->getCheckoutDetails($reference);

		// set default payment method
		$method = 'Default';

		// get payment method
		if (isset($details['transaction_extras']['payment']['payment_name'])) {
			$method = $details['transaction_extras']['payment']['payment_name'];
		}

		// case what status should we used
		$status = Configuration::get('PS_OS_PREPARATION');

		// get cart object
		$cart 	  = $this->context->cart;
		// get customer object
		$customer = new Customer($cart->id_customer);
		// get currency object
		$currency = $this->context->currency;
		// get total cart valie
		$total 	  = (float) $cart->getOrderTotal(true, Cart::BOTH);
		// additional email variables
		$mailVars = array();

		// get all addresses
		$addresses = $this->context->customer->getAddresses($this->context->language->id);

		// if customer has no address get address from paymuna
		if (empty($addresses)
			&& isset($details['transaction_addresses']['billing'])) {
			$this->createAddress($cart, $customer, $details['transaction_addresses']['billing']);
		}

		// save and validate order
		$this->module->validateOrder($cart->id, $status, $total, $this->module->displayName . '-' . $method, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);

		// redirect to order confirmation
		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key.'&session='.$session);
	}

	/**
	 * Create customer address if does not exists.
	 *
	 * @param 	object
	 * @param 	object
	 * @param 	array
	 * @return 	array
	 */
	public function createAddress($cart, $customer, $address) {
		// get database instance
		$db = Db::getInstance();

		// initialize sql query
		$sql = sprintf('SELECT * FROM %scountry WHERE iso_code="%s"', _DB_PREFIX_, $address['address_country']);

		// get the country information
		$country = $db->getRow($sql);

		// default to PH
		if (!$country || empty($country)) {
			$sql = sprintf('SELECT * FROM %scountry WHERE iso_code="%s"', _DB_PREFIX_, 'PH');
			$country = $db->getRow($sql);
		}

		// create address data
		$data = array(
			'id_country'   => $country['id_country'],
			'id_customer'  => $customer->id,
			'alias' 	   => $alias,
			'lastname' 	   => $customer->lastname,
			'firstname'    => $customer->firstname,
			'address1' 	   => $address['address_street'],
			'address2'	   => '',
			'postcode' 	   => $address['address_postal'],
			'city' 		   => $address['address_city'],
			'other' 	   => 'Additional Information',
			'phone' 	   => $address['address_phone'],
			'phone_mobile' => $address['address_phone'],
			'date_add' 	   => date('Y-m-d H:i:s'),
			'date_upd' 	   => date('Y-m-d H:i:s'),
			'active' 	   => 1,
			'deleted' 	   => 0
 		);

		// execute insert query
		$db->insert('address', $data, true, false, Db::REPLACE, true);

		// get last inserted id
		$id = $db->Insert_ID();

		// initialize sql query
		$sql = sprintf('SELECT * FROM %saddress WHERE id_address=%s', _DB_PREFIX_, $id);

		// get the address
		$address = $db->getRow($sql);

		return $address;
	}
}
