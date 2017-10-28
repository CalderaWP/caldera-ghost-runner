<?php


namespace calderawp\ghost\Client;
use calderawp\ghost\Factories;


/**
 * Class Results
 * @package calderawp\ghost\Client
 */
class Results extends Base{


	/**
	 * Get test results by result ID
	 *
	 * @param string $id Result ID
	 *
	 * @return array
	 */
	public function result( $id )
	{
		$key = md5( CGR_VER . __CLASS__ .  $id . __METHOD__ );
		if( ! is_array( $results = get_transient( $key ) ) ) {


			$rUrl = add_query_arg( $this->queryArgs(), $this->getApiUrl() . "/results/$id" );
			$body = $this->requestGet( $rUrl );
			if ( is_object( $body ) && isset( $body->code ) && ! 'SUCCESS' == $body->code ) {
				return array(
					'incomplete' => true,
					'passing'    => false,
				);
			}

			$results = array(
				'incomplete' => false,
				'passing' => $body->data->passing,
				'videoUrl' =>  $body->data->videoUrl,
				'formUrl' => $body->data->startUrl,
				'testUrl' => isset( $body->data->test->_id ) ? Factories::testUrl(  $body->data->test->_id  ): ''
			);

			wp_cache_set( $key, $results, 599 );
		}


		return $results;
		
	}
}