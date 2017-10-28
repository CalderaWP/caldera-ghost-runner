<?php


namespace calderawp\ghost\Client;


/**
 * Class Base
 * @package CalderaWP\GhostInspector\Client
 */
abstract class Base {

	/**
	 * @var string
	 */
	private  $apiKey;

	/**
	 * @var string
	 */
	private  $apiURL = 'https://api.ghostinspector.com/v1';

	/**
	 * Base constructor.
	 *
	 * @param string $apiKey
	 */
	public function __construct( $apiKey )
	{

		$this->apiKey = $apiKey;
	}

	/**
	 * @return string
	 */
	protected function getApiKey()
	{
		return $this->apiKey;
	}

	/**
	 * @return string
	 */
	protected function getApiUrl()
	{
		return $this->apiURL;
	}

	/**
	 * Create query args for GET requests
	 *

	 * @param array $args Optional. Additional arguments to pass
	 *
	 * @return array
	 */
	protected function queryArgs(  array $args = array() )
	{
		return array_merge( array(
			'apiKey' => $this->apiKey
		), $args );
	}

	/**
	 * Make a GET Request
	 *
	 * @param string $url Request URL
	 * @param array $headers Optional. request headers. Default is empty array.
	 *
	 * @return \Requests_Response
	 */
	protected function get( $url, array $headers = array() ){
		return \Requests::get( $url, $headers );
	}

	/**
	 * Make a POST Request
	 *
	 * @param string $url Request URL
	 * @param array $data Optional. Request data. Default is empty array.
	 * @param array $headers Optional. request headers. Default is empty array.
	 *
	 * @return \Requests_Response
	 */
	protected function post( $url, array $data = array(), array  $headers= array() ){
		return \Requests::post( $url, $headers, $data );
	}

	/**
	 * Run get request and return body
	 *
	 * @param $rUrl
	 *
	 * @return array|bool|mixed|object
	 */
	protected function requestGet( $rUrl )
	{
		/** @var \Requests_Response $result */
		$result = $this->get( $rUrl );

		if ( 200 != $result->status_code ) {
			return false;
		}

		$body = json_decode( $result->body );

		return $body;
	}

	/**
	 * Return ID of request body
	 * @param object|mixed $body
	 *
	 * @return bool|string
	 */
	protected function returnId( $body )
	{
		return is_object( $body ) && isset( $body->data, $body->data->_id ) ? $body->data->_id : false;
	}
}