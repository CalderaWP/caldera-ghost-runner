<?php
/**
 Plugin Name: Ghost Inspector Test Runner
 Version: 0.1.0
 */
use \calderawp\ghost\Container as Container;

define( 'CGR_VER', '0.1.0' );


add_action( 'init', function(){
	include_once  __DIR__ . '/vendor/autoload.php';
	if( defined( 'CFCORE_VER' ) ){
		calderaGhostRunner();
	}else{

	}

});

/**
 * Get main instance of container
 *
 * @return Container
 */
function calderaGhostRunner(){
	static  $calderaGhostInspector;

	if( ! is_object( $calderaGhostInspector ) ){
		include_once  __DIR__ . '/vendor/autoload.php';
		$calderaGhostInspector = new Container();
		/**
		 * Runs when main instance of container is initialized
		 *
		 * @param Container $calderaGhostInspector
		 */
		do_action( 'calderaGhostRunner.init', $calderaGhostInspector );
	}

	return $calderaGhostInspector;

}

/**
 * Run all tests with on a specific sits
 *
 * @param string $siteUrl Optional. URL to run on. Default is result of site_url();
 *
 * @return array
 */
function calderaGhostRunnerRun( $siteUrl = null ){
	$siteUrl = is_string( $siteUrl ) && filter_var( $siteUrl, FILTER_VALIDATE_URL ) ? $siteUrl : site_url();
	return calderaGhostRunner()
		->getRunner()
		->setRootUrl( esc_url_raw( $siteUrl ) )
		->allTests();
}

/**
 * Set API key from ENV var or constant CGRKEY
 */
add_action( 'calderaGhostRunner.init',
	function( Container $container ){
		$apiKey = calderaGhostRunnerEnv( 'CGRKEY', null );
		$container->setApiKey( $apiKey );
	},
	0
);

/**
 * Set tests from the spreadsheet
 */
add_action( 'calderaGhostRunner.init',
	function( Container $container ){
		$id = calderaGhostRunnerEnv( 'CGRGDID' );
		\calderawp\ghost\Factories::testsFromGoogleSheet( $id, $container );
	},
	2
);

/**
 * Make some REST API endpoints
 */
add_action( 'calderaGhostRunner.init',
	function( Container $container ){
		add_action( 'rest_api_init',
			function () use( $container )
			{
				$permissions  = function ( \WP_REST_Request $r ) use ( $container ) {
					$key = $r->get_param( 'key' );
					return hash_equals( $key, $container->getLocalApiKey() );
				};

				register_rest_route( 'ghost-runner/v1', 'tests/all', array(
					'methods'     => 'GET',
					'permission_callback' => $permissions,
					'callback'    => function ( \WP_REST_Request $r ) use ( $container ) {
						return rest_ensure_response(
							$container
								->getRunner()
								->setRootUrl(
									$r->get_param( esc_url_raw( 'rootUrl' ) )
								)
								->toApiResponse()
						);
					},
				) );

				register_rest_route( 'ghost-runner/v1', 'tests/result', array(
					'methods'     => 'GET',
					'permission_callback' => $permissions,
					'callback'    => function ( \WP_REST_Request $r ) use ( $container ) {
						return rest_ensure_response(
							$container
								->getResultsClient()
								->result(
									$r->get_param( strip_tags( stripslashes( 'id' ) ) )
								)
						);
					},
				) );
			}
		);
	},
	4
);

/**
 * Make a hacky admin that works, but like, LOL get rid of this.
 */
add_action( 'calderaGhostRunner.init',
	function( Container $container ){
		add_action( 'admin_menu', function() use ( $container ) {
			add_menu_page(
				'Ghost Runner',
				'Ghost Runner',
				'manage_options',
				$container::SLUG,
				function() use( $container ){
					$apiUrl = $action = add_query_arg(
						array(
							'key' => $container->getLocalApiKey()
						),
						rest_url( 'ghost-runner/v1/tests/all' )
					);

					$importAction = 'importTests';

					$allRunAction = 'allRun';

					$action = $container->adminUrl( array(), $allRunAction );

					$testRunAction = \calderawp\ghost\Test::ACTION;

					$branchIdentifier = 'setBranch';

					/**
					 * Check if nonce is passed and valid by action.
					 *
					 * This is actually the route essentially -- each part has an "action" that action is used as a GET var whose value is nonce. This function checks if that "action" is set and the nonce is valid.
					 *
					 * @param string $action Nonce actio
					 *
					 * @return bool
					 */
					$testNonce = function ( $action ){
						return isset( $_GET[ $action ] ) && wp_verify_nonce( $_GET[ $action ], $action );
					};

					$importUrl = $container->adminUrl( array('hi' => 'Roy' ), $importAction );

					$tests = $container->getTests();

					wp_enqueue_script( $container::SLUG, plugin_dir_url( __FILE__ ) . 'assets/admin.js', array( 'jquery' ) );

					//Show results of all of the tests runnin
					if( isset( $_GET[ 'rootUrl' ] ) && $testNonce( $allRunAction ) ){

						$key = md5( CGR_VER . $allRunAction . $action );
						if( ! is_array( $results = get_transient( $key ) ) ){
							$results = $container
								->getRunner()
								->setRootUrl(
									esc_url_raw( $_GET[ 'rootUrl' ] )
								)
								->toArray();
							set_transient( $key, $results, 599 );
						}


						printf( '<div id="ghost-runner">Results!</div><script> window.CGRResults = %s;</script>', wp_json_encode( $results ) );

						wp_localize_script( $container::SLUG, 'CGRResults', $results  );
					}
					//run single test
					elseif ( $testNonce( $testRunAction ) ){
						$id = ( isset( $_GET[ 'id' ] ) && 0 < absint( $_GET[ 'id' ] ) ) ? $_GET[ 'id' ] : 0;
						if( 0 < $id ){
							$test = $container->getTest( $id );
							if( $test ){
								echo "Results For Test $id";
								$rId =  $container
									->getRunner()
									->setRootUrl( site_url() )
									->test( $id );
								$resluts = $container->getResultsClient()->result( $rId );
								var_dump( $rId );
								var_dump( $resluts );
							}
						}

					}
					//Show the all runner and importer
					else{
						echo '<h3>Run All Tests On A Specific URL</h3>';

						printf(
							'<form id="ghost-runner-form" action="%s"><label>URL For Sites</label><input name="rootUrl" type="text" />%s</form>',
							esc_url_raw( $action ),
							wp_nonce_field( $allRunAction, $allRunAction, false, false ) . get_submit_button( 'Run All Tests' ) . '<input type="hidden" name="page" value="' . $container::SLUG . '" />'

						);


						if ( $testNonce( $importAction ) ) {
							\calderawp\ghost\Factories::import();
							echo '<div>IMPORTED:)</div>';
						}else{
							echo '<h3>Import Tests</h3>';
							echo '<strong>This will delete all pages and all forms</strong>';
							printf( '<a href="%s">Import Forms</a>', esc_url( $importUrl ) );
						}
					}

					//list all tests
					$linkPattern = '<div class="ghost-runner-test-list">%s :<a href="%s">Page</a> - <a href="%s">Run</a>';
					echo '<h3>Run A Single Test Using This Site</h3>';
					/** @var \calderawp\ghost\Test $test */
					foreach ( $tests as $test ){
						printf( $linkPattern,
							esc_html( $test->getName() ),
							esc_url(
								get_permalink(
									\calderawp\ghost\Factories::pageByGhostId( $test->getId()
									)
								)
							),
							esc_url( $test->runLink() )
						);
					}

					//Set branch
					$nameAttr = $branchIdentifier . '-val';
					if( isset( $_GET[ $nameAttr ] ) && $testNonce( $branchIdentifier )  ){
						update_option( $branchIdentifier, trim( strip_tags( $_GET[ $nameAttr ] ) ) );
						if ( defined( 'CFCORE_BASENAME' ) ) {
							$plugin = new calderawp\ghost\Plugins\Plugin( get_option( $branchIdentifier )  );
							$plugin->update(CFCORE_BASENAME );
						}
					}
					echo  'Set Git Branch';
					$action = $container->adminUrl( array(), $branchIdentifier );

					printf( '<form action="%s" style="border:1px solid black;" id="ghost-runner-change-git-branch"><label>Branch</label><input type="text" name="%s" value="%s" />%s</form> ',
						esc_url_raw( $action ),
						esc_attr( $nameAttr ),
						esc_attr( get_option( $branchIdentifier ) ),
						get_submit_button( 'Set branch' ) . wp_nonce_field( $branchIdentifier, $branchIdentifier, false, false ) . '<input type="hidden" value="' . $container::SLUG . '" name="page" />'

					);

				}
			);
		});

	}
);




/**
 * Get the URL for the form editor
 *
 * @param string $formId Form ID
 *
 * @return string
 */
function calderaGhostRunnerFormUrl( $formId ){
	$admin = \Caldera_Forms_Admin::get_instance();
	if( method_exists( $admin, 'form_edit_link' ) ) {
		return \Caldera_Forms_Admin::form_edit_link( $formId );
	}else{
		$args = array(
			'edit' => $formId,
			'page' => \Caldera_Forms::PLUGIN_SLUG
		);

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}
}

/**
 * Get value form ENV var, constant or default in that order.
 *
 * @param string $var Name of variable
 * @param null|mixed $default Optional. Default value if not set in either location. Default is null.
 *
 * @return mixed|null
 */
function calderaGhostRunnerEnv( $var, $default = null ){
	$value = getenv( $var );
	if( is_null( $value ) && defined( strtoupper( $var ) ) ){
		$value = constant( strtoupper( $var ) );
	}

	if( is_null( $value ) ){
		$value = $default;
	}

	/**
	 * Change value of env var
	 */
	return apply_filters( 'calderaGhostRunner.env.'. $var, $value );

}