<?php // -->

include dirname(__FILE__) . '/../../../lib/PayMunaBase.php';

/**
 * PayMuna Prestashop Extension.
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 *
 * @vendor 	Openovate Labs
 * @author 	Charles Zamora <czamora@openovate.com>
 */
class OrderController extends OrderControllerCore
{
	/**
	 * Initialize front controller.
	 *
	 * @return 	this
	 */
    public function init()
    {
		// is the user logged in?
		if(!$this->context->customer->isLogged(true)) {
			// check order process of opc
			$orderType = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';

			// get order url
			$back_url = $this->context->link->getPageLink($orderType, true, (int)$this->context->language->id, $params);

			// set url parameters
			$params = array('back' => $back_url);

			// redirect to log in page
			Tools::redirect($this->context->link->getPageLink('authentication', true, (int)$this->context->language->id, $params));
		}

		$configuration = unserialize(Configuration::get('PAYMUNA'));

		if(!isset(
			$configuration['PAYMUNA_API_TOKEN'],
			$configuration['PAYMUNA_API_SECRET'],
			$configuration['PAYMUNE_ENVIRONMENT'],
			$configuration['PAYMUNA_REFERENCE'])
		) {
			Tools::redirect('/');
		}

		$token  = $configuration['PAYMUNA_API_TOKEN'];
		$secret = $configuration['PAYMUNA_API_SECRET'];
		$env    = $configuration['PAYMUNE_ENVIRONMENT'];

		$paymuna = new PayMunaBase($token, $secret, $env);

		parent::init();

		$cart 	  = $this->context->cart;
		$summary  = $this->context->cart->getSummaryDetails();
		$products = $this->context->cart->getProducts(true);

		foreach($products as $product) {
			$image = $this->context->link->getImageLink(
					 $product['link_rewrite'],
					 $product['id_image'], 'home_default');

			$link  = 'http://'. $_SERVER['HTTP_HOST'] .'/index.php?controller=product&id_product=' . $product['id_product'];

			$paymuna->addItem(
				$product['name'],
				$product['total'],
				$image,
				$link,
				$product['cart_quantity']
			);
		}

		$shipping = $summary['total_shipping'];
		$tax 	  = $summary['total_tax'];

		if ($summary['total_shipping_tax_exc'] > 0) {
			$shipping = $summary['total_shipping_tax_exc'];
		}

		if ($shipping > 0) {
			// set shipping cost on transaction extras
			$paymuna->addExtra('shipping_cost', $summary['total_shipping_tax_exc']);
		}

		if($tax > 0) {
			// set tax on transaction extras
			$paymuna->addExtra('tax', $tax);
		}

		$currency = $this->context->currency->iso_code;

		// get all addresses
		$addresses = $this->context->customer->getAddresses($this->context->language->id);

		if (!empty($addresses)) {
			// use address for both shipping and billing
			$address = $addresses[0];

			// get the customer details
			$customer = $this->context->customer;

			// get the fullname
			$fullname = $customer->firstname . ' ' . $customer->lastname;

			// get country iso code
			$country = $this->context->country->iso_code;
			$city = 'N/A';

			if(isset($address['city']) && $address['city'] != '') {
				$city = $address['city'];
			}

			$state = 'N/A';

			// if state is set
			if($address['id_state'] != 0) {
				// get the state by id
				$state = new State(intval($address['id_state']));

				// get the state
				$state = $state->name;
			}
			
			$paymuna->addAddress(
				$address['alias'],
				$address['address1'],
				$city,
				$state,
				$country,
				$address['postcode'],
				null,
				$fullname,
				$customer->email,
				$address['phone_mobile']
			);
		}

		$redirect = $paymuna
			->setRedirect('http://' . $_SERVER['HTTP_HOST'] . '/module/paymuna/confirmation')
			->setCallbackUrl('http://' . $_SERVER['HTTP_HOST'] . '/order')
			->setReference($configuration['PAYMUNA_REFERENCE'])
			->getCheckoutUrl();

		Tools::redirect($redirect);
    }
}
