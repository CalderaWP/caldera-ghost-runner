<?php


namespace calderawp\ghost;
use calderawp\ghost\Client\Results;
use calderawp\ghost\Client\Tests;
use calderawp\ghost\Test;


/**
 * Class Container
 * @package CalderaWP\GhostInspector
 */
class Container extends \Pimple\Container {


	const ApiKeyOffset = 'apiKey';

	const TestsClientOffset = 'testsClient';

	const ResultsClientOffset = 'resultsClient';

	const TestsCollectionOffset = 'testsCollection';

	const RunnerOffset = 'runnerOffset';

	const  LocalApiKeyOffset = 'localApiKey';

	const SLUG = 'caldera-ghost-runner';

	/**
	 * @param string $apiKey
	 */
	public function setApiKey( $apiKey )
	{
		$this->offsetSet( self::ApiKeyOffset, $apiKey );
	}

	/**
	 * @return string
	 */
	public function getApiKey()
	{
		return $this->offsetGet( self::ApiKeyOffset );
	}

	/**
	 * Add a test to the collection
	 *
	 * @param Test $test
	 *$siteUrl
	 * @return $this
	 */
	public function addTest( Test $test )
	{

		if( ! $this->offsetExists(  self::TestsCollectionOffset ) ){
			$tests = array();
		}else{
			$tests = $this->offsetGet( self::TestsCollectionOffset );
		}

		$tests[ $test->getId() ] = $test;
		$this->offsetSet( self::TestsCollectionOffset, $tests );
		return $this;
	}

	/**
	 * Get test runner
	 *
	 * @return Runner
	 */
	public function getRunner()
	{
		if( ! $this->offsetExists( self::RunnerOffset ) ){
			$this->offsetSet( self::RunnerOffset, new Runner( $this ) );
		}

		return $this->offsetGet( self::RunnerOffset );
	}



	/**
	 * Link to admin page.
	 *
	 * @param array $args Optional. Additional query args.
	 * @param string|bool $action Optional. If string query arg of that name, whose value is a nonce generated with that action will be added.
	 *
	 * @return string
	 */
	public function adminUrl( array  $args = array( ), $action = false )
	{
		if( $action ){
			$args[ $action ] = wp_create_nonce( $action );
		}

		return add_query_arg(
			wp_parse_args( $args, array(
				'page' => self::SLUG
			)
		), admin_url( 'admin.php' ) );
	}

	/**
	 * @return array
	 */
	public function getTests()
	{
		if( ! $this->offsetExists(  self::TestsCollectionOffset ) ){
			self::offsetSet( self::TestsCollectionOffset, array() );
		}

		return $this->offsetGet( self::TestsCollectionOffset );
	}

	public function getLocalApiKey()
	{
		return $this->get( self::LocalApiKeyOffset );
	}

	/**
	 * Get item by identifier form container, with fallback to environment var or constant (transformed to right form)
	 *
	 * @param $identifier
	 *
	 * @return mixed|null
	 */
	public function get( $identifier  ){
		return $this->offsetExists( $identifier ) ? $this->offsetGet( $identifier ) : calderaGhostRunnerEnv( 'CGR' . strtoupper( $identifier ) );
	}

	/**
	 * Get a test from collection
	 *
	 * @param  string $id Test ID
	 *
	 * @return Test|null
	 */
	public function getTest( $id ){
		$tests = $this->getTests();
		if(isset( $tests[ $id ] ) ){
			return $tests[ $id ];
		}

		return null;
	}

	/**
	 * Get API client for Ghost Inspector tests
	 *
	 * @return Tests
	 */
	public function getTestsClient(){
		if( ! $this->offsetExists( self::TestsClientOffset ) ){

			$this->offsetSet(
				self::TestsClientOffset,
				new Tests( $this->getApiKey() )
			);
		}

		return $this->offsetGet( self::TestsClientOffset );
	}

	/**
	 * Get API client for Ghost Inspector test results
	 *
	 * @return Results
	 */
	public function getResultsClient()
	{
		if( ! $this->offsetExists( self::ResultsClientOffset ) ){

			$this->offsetSet(
				self::ResultsClientOffset,
				new Results( $this->getApiKey() )
			);
		}

		return $this->offsetGet( self::ResultsClientOffset );
	}
}
