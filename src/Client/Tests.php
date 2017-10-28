<?php


namespace calderawp\ghost\Client;


/**
 * Class Tests
 * @package CalderaWP\GhostInspector\Client
 */
class Tests extends Base{


	/**
	 * @param $id
	 * @param $startUrl
	 * @param bool $immediate
	 *
	 * @return bool|string
	 */
	public function runTest( $id, $startUrl, $immediate = false ){
		$rUrl = sprintf( '%s/tests/%s/execute', $this->getApiUrl(), $id );
		$rUrl .= '?' . http_build_query(
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