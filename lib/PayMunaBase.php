<?php // -->

/**
 * PayMuna Prestashop Extension.
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 *
 * @vendor 	Openovate Labs
 * @author 	Charles Zamora <czamora@openovate.com>
 */
class PayMunaBase {
	/**
	 * Base url for paymuna.
	 *
	 * @const string
	 */
	const TEST_URL = 'http://paymuna.dev';
	const LIVE_URL = 'http://paymuna.com';

	/**
	 * API Token
	 *
	 * @var string | null
	 */
	protected $token  = null;

	/**
	 * API Secret
	 *
	 * @var string | null
	 */
	protected $secret = null;

	/**
	 * Set of items
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Set of address
	 *
	 * @var array
	 */
	protected $address = array();

	/**
	 * Default redirect url.
	 *
	 * @var string
	 */
	protected $redirect = null;

	/**
	 * Default callback url
	 *
	 * @var string
	 */
	protected $callback = null;

	/**
	 * Default checkout reference
	 *
	 * @var string
	 */
	protected $reference = null;

	/**
	 * Default checkout api url
	 *
	 * @var string
	 */
	protected $url = null;

	/**
	 * Default checkout extras
	 *
	 * @var string
	 */
	protected $extras = [];

	/**
	 * Auto submit flag
	 *
	 * @var bool
	 */
	protected $autoSubmit = false;

	/**
	 * Set access token and access secret
	 *
	 * @param 	string | null
	 * @param 	string | null
	 * @return 	this
	 */
	public function __construct($token = null, $secret = null, $env = null)
	{
		// set the access token
		$this->token = $token;
		// set the access secret
		$this->secret = $secret;

		// set the api url
		if ($env == 'test') {
			$this->url = self::TEST_URL;
		} else {
			$this->url = self::LIVE_URL;
		}
	}

	/**
	 * Add item to cart.
	 *
	 * @param 	string 	Item name
	 * @param 	number 	Item price
	 * @param 	string 	Item image
	 * @param 	string 	Item link
	 * @param 	string 	Item quantity
	 * @return 	this
	 */
	public function addItem($name, $price, $image, $link, $quantity)
	{
		// add item to the items list
		$this->items[] = array(
			'item_name' 	=> $name,
			'item_price' 	=> $price,
			'item_image' 	=> array('file_path' => $image),
			'item_link' 	=> $link,
			'item_quantity' => $quantity
		);

		return $this;
	}

	/**
	 * Add address.
	 *
	 * @param 	string 	Address label
	 * @param 	string 	Address Street
	 * @param 	string 	Address city
	 * @param 	string  Address state
	 * @param  	string 	Address country
	 * @param 	string 	Address postal
	 * @return 	this
	 */
	public function addAddress(
		$label, $street, $city,
		$state, $country, $postal, $index,
		$fullname = '', $email = '', $phone = ''
	)
	{
		$address = array(
			'address_fullname' => $fullname,
			'address_email' => $email,
			'address_phone' => $phone,
			'address_country' => $country,
			'address_state' => $state,
			'address_city' => $city,
			'address_street' => $street,
			'address_postal' => $postal,
		);

		$this->address['billing'] = $address;
		$this->address['shipping'] = $address;

		return $this;
	}

	/**
	 * Add extra values to checkout
	 *
	 * @param 	string 	field
	 * @param 	string 	value
	 * @return 	this
	 */
	public function addExtra($field, $value)
	{
		// set extra field
		$this->extras[$field] = $value;

		return $this;
	}

	/**
	 * Set the redirect after checkout.
	 *
	 * @param 	string
	 * @return 	this
	 */
	public function setRedirect($url)
	{
		// set redirect url after checkout
		$this->redirect = $url;

		return $this;
	}

	/**
	 * Set the callback url for order
	 * updates.
	 *
	 * @param 	string
	 * @return 	this
	 */
	public function setCallbackUrl($url)
	{
		// set callback url
		$this->callback = $url;

		return $this;
	}

	/**
	 * Set the reference id for checkout
	 * session if there are any.
	 *
	 * @param 	string
	 * @return 	this
	 */
	public function setReference($reference)
	{
		// set the reference
		$this->reference = $reference;

		return $this;
	}

	/**
	 * Generates checkout url.
	 *
	 * @return 	array
	 */
	public function getCheckoutUrl()
	{
		// prepare query parameters
		$params = array(
			// set the client id
			'client_id' 		 => $this->token,
			// set the client secret
			'client_secret' 	 => $this->secret,
			// set the checkout reference
			'checkout_reference' => $this->reference
		);

		// format query params
		$query = http_build_query($params);

		// prepare the body parameters
		$data = array(
			// set transaction callback
			'transaction_callback' => $this->callback,
			// set redirect url
			'transaction_redirect' => $this->redirect,
			// set transaction items
			'transaction_items' => $this->items,
		);

		// set transaction addresses
		if (!empty($this->address)) {
			$data['transaction_addresses'] = $this->address;
		}

		// set transaction extras 
		if (!empty($this->extras)) {
			$data['transaction_extras'] = $this->extras;
		}

		// set request url
		$url = $this->url . '/rest/transaction/create?' . $query;

		// send post request
		$response = $this->__sendPost($url, $data);

		// is there an error?
		if($response['error']
		|| !is_array($response['results'])
		|| !isset($response['results']['checkout_url'])) {
			return null;
		}

		return $response['results']['checkout_url'];
	}

	/**
	 * Get checkout details.
	 *
	 * @param 	string 	Reference
	 * @return  array
	 */
	public function getCheckoutDetails($reference)
	{
		$configuration = unserialize(Configuration::get('PAYMUNA'));

		// set query params
		$params = array(
			'client_id' => $this->token,
			'client_secret' => $this->secret
		);

		// set the url
		$url = $this->url . '/rest/transaction/detail/' . $reference;

		// send the request and get the response
		$response = $this->__sendGet($url, $params);

		// is there an error?
		if($response['error']
		|| !is_array($response['results'])
		) {
			return null;
		}

		return $response['results'];
	}

	/**
	 * Send post request.
	 *
	 * @param 	string
	 * @param 	array
	 * @return 	array
	 */
	private function __sendPost($url, $data = array())
	{
		// initialize curl
		$curl = curl_init();

		// set curl options
		curl_setopt_array($curl, array(
		  	CURLOPT_URL 			 => $url,
		  	CURLOPT_RETURNTRANSFER 	 => true,
		  	CURLOPT_ENCODING 		 => "",
		  	CURLOPT_MAXREDIRS 	 	 => 10,
		  	CURLOPT_TIMEOUT 		 => 30,
		  	CURLOPT_HTTP_VERSION   	 => CURL_HTTP_VERSION_1_1,
		  	CURLOPT_CUSTOMREQUEST  	 => "POST",
		  	CURLOPT_POSTFIELDS 	 	 => http_build_query($data)
		));

		// execute curl
		$response = curl_exec($curl);
		// catch curl error
		$err 	  = curl_error($curl);

		// get the json response
		$response = json_decode($response, true);

		// close connection
		curl_close($curl);

		return $response;
	}

	/**
	 * Send get request.
	 *
	 * @param 	string
	 * @param 	array
	 * @return 	array
	 */
	private function __sendGet($url, $data = array())
	{
		// initialize curl
		$curl = curl_init();

		// set curl options
		curl_setopt_array($curl, array(
		  	CURLOPT_URL 			 => $url . '?' . http_build_query($data),
		  	CURLOPT_RETURNTRANSFER 	 => true,
		  	CURLOPT_ENCODING 		 => "",
		  	CURLOPT_MAXREDIRS 	 	 => 10,
		  	CURLOPT_TIMEOUT 		 => 30,
		  	CURLOPT_HTTP_VERSION   	 => CURL_HTTP_VERSION_1_1,
		  	CURLOPT_CUSTOMREQUEST  	 => "GET"
		));

		// execute curl
		$response = curl_exec($curl);
		// catch curl error
		$err 	  = curl_error($curl);

		// get the json response
		$response = json_decode($response, true);

		// close connection
		curl_close($curl);

		return $response;
	}
}
