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
        if( ! $this->get( self::ApiKeyOffset )  ){
            return isset($_ENV, $_ENV['CGRKEY']) ? $_ENV ['CGRKEY'] : CGRKEY;
        }
		return $this->offsetGet( self::ApiKeyOffset );
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


	public function getLocalApiKey()
	{
	    if( ! $this->get( self::LocalApiKeyOffset )  ){
	        return isset($_ENV, $_ENV['CGRLOCALAPIKEY']) ? $_ENV ['CGRLOCALAPIKEY'] : CGRLOCALAPIKEY;
        }
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


}
