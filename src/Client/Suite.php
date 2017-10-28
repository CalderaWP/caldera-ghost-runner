<?php


namespace calderawp\ghost\Client;


/**
 * Class Suite
 * @package calderawp\ghost\Client
 */
class Suite  extends Base {

	protected $id;
	public function setSuiteId( $id ){
		$this->id = $id;
	}
	public function runSuite( $startUrl = false, $immediate = false ){
		if( ! filter_var( $startUrl, FILTER_VALIDATE_URL ) ){
			$startUrl = home_url();
		}
		$rUrl = sprintf( '%s/tests/%s/execute', $this->getApiUrl(), $this->id )
	        . '?' . http_build_query(
			$this->queryArgs(
				array(
					'startUrl' => urlencode( $startUrl ),
					'immediate' => intval( $immediate )
				)
			)
		);

		$body = $this->requestGet( $rUrl );

		return $this->returnId( $body );
	}
}