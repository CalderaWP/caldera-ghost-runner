<?php


namespace calderawp\ghost;


/**
 * Class Runner
 * @package calderawp\ghost
 */
class Runner {

	/**
	 * @var Container
	 */
	protected  $container;

	protected  $testClient;

	/** @var  string */
	protected  $rootUrl;

	public function __construct( Container $container )
	{
		$this->container = $container;
		$this->testClient = $container->getTestsClient();

	}

	/**
	 * (re)set the Url to run tests on
	 *
	 * @param string $rootUrl Root URL for tests.
	 *
	 * @return $this
	 */
	public function setRootUrl( $rootUrl )
	{
		$this->rootUrl = $rootUrl;
		return $this;
	}


	/**
	 * Run all tests
	 *
	 * @return array
	 */
	public function allTests()
	{
		$results = array();
		foreach ( $this->container->getTests() as $test ){
			$results[] = $this->runTest( $test );
		}

		return $results;
	}

	/**
	 * Run all tests and return as REST API response object
	 *
	 * @param null|string $rootUrl Optional. URL for site to run on. If not the current value of $this->rootUrl is used.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function toApiResponse( $rootUrl = null )
	{

		return rest_ensure_response(
			$this->toArray( $rootUrl )
		);

	}

	/**
	 * Run all tests and return as array
	 *
	 * @param null|string $rootUrl Optional. URL for site to run on. If not the current value of $this->rootUrl is used.
	 *
	 * @return array
	 */
	public function toArray( $rootUrl = null )
	{
		if ( $rootUrl ) {
			$this->setRootUrl( $rootUrl );
		}

		$results = $this->allTests();

		$resultUrls = array();
		foreach ( $results as $result ) {
			$resultUrls[] = esc_url_raw( add_query_arg( array(
					'id'  => $result,
					'key' => $this->container->getLocalApiKey()
				), rest_url( 'ghost-runner/v1/tests/result' ) ) );
		}


		return  array(
				'testIds'    => $results,
				'resultUrls' => $resultUrls
		);

	}

	/**
	 * Run one test by ID
	 *
	 * @param string $id Test ID
	 *
	 * @return bool|string
	 */
	public function test( $id )
	{
		$test = $this->container->getTest( $id );
		if( $test ){
			return $this->runTest( $test );
		}
		return null;

	}

	/**
	 * Run a test
	 *
	 * @param Test $test
	 *
	 * @return bool
	 */
	protected function runTest( Test $test )
	{
		return  $test->runOn( $this->rootUrl, true );

	}
}