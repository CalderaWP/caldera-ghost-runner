<?php
/**
 Plugin Name: Ghost Inspector Test Runner
 Version: 0.3.1
 */
use \calderawp\ghost\Container as Container;
define( 'CGR_VER', '0.3.1' );


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
 * WP CLI Command
 */
if ( class_exists( 'WP_CLI' ) ) {
    $importCommand = function() {
        \calderawp\ghost\Factories::import();
        WP_CLI::success( "The test forms may or may not have imported" );
    };
    WP_CLI::add_command( 'cgr import', $importCommand );
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
				function() use( $container ) {
                    $apiUrl = $action = add_query_arg(
                        array(
                            'key' => $container->getLocalApiKey()
                        ),
                        rest_url('ghost-runner/v1/tests/all')
                    );

                    $importAction = 'importTests';

                    $allRunAction = 'allRun';

                    $action = $container->adminUrl(array(), $allRunAction);

                    $testRunAction = \calderawp\ghost\Test::ACTION;



                    /**
                     * Check if nonce is passed and valid by action.
                     *
                     * This is actually the route essentially -- each part has an "action" that action is used as a GET var whose value is nonce. This function checks if that "action" is set and the nonce is valid.
                     *
                     * @param string $action Nonce actio
                     *
                     * @return bool
                     */
                    $testNonce = function ($action) {
                        return isset($_GET[$action]) && wp_verify_nonce($_GET[$action], $action);
                    };

                    $importUrl = $container->adminUrl(array('hi' => 'Roy'), $importAction);




                    if( $testNonce($importAction) ){
                        if ( $testNonce( $importAction ) ) {
                            \calderawp\ghost\Factories::import();
                            echo '<div>IMPORTED:)</div>';
                            printf( '<a href="%s">Import Forms Again</a>', esc_url( $importUrl ) );
                        }
                    }else{
                        echo '<h3>Import Tests</h3>';
                        echo '<strong>This will delete all pages and all forms</strong>';
                        printf('<a href="%s">Import Forms</a>', esc_url($importUrl));

                    }

                    echo '<h3>Forms</h3>';
                    $query = new WP_Query(
                        array(
                            'post_type' => 'page',
                            'posts_per_page' => '999',
                            'meta_query' => array(
                                'key' => 'GCR',
                                'value' => 'yes',
                                'compare' => '='
                            )

                        )
                    );

                    $linkPattern = '<div class="ghost-runner-test">%s - <a href="%s">Form</a> - <a href="%s">Page</a> - <a href="%s">Git Issue</a>';


                    if( $query->have_posts() ){
                        foreach ( $query->posts as $post ){
                            $editLink = add_query_arg(
                                'edit',
                                get_post_meta( $post->ID, 'CGR_formId', true ),
                                admin_url( 'admin.php?page=caldera-forms' )
                            );

                            $gitLink = 'https://github.com/calderawp/caldera-forms/' . get_post_meta( $post->ID, 'CGR_gitIssue', true );
                            printf(
                                $linkPattern,
                                esc_html( $post->post_title ),
                                esc_url( $editLink ),
                                esc_url( get_permalink( $post ) ),
                                esc_url( $gitLink )
                            );
                        }
                    }





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