<?php


namespace calderawp\ghost;
use calderawp\ghost\Entities\Test;


/**
 * Class Import
 * @package calderawp\ghost
 */
class Import {

	/**
	 * @var array
	 */
	protected $tests;

	/**
	 * Import constructor.
	 *
	 * @param array $tests Should be provided by sheet 4
	 */
	public function __construct( $tests )
	{
		$this->tests = $tests;
	}

	/**
	 * Do the import process
	 */
	public function run()
	{
		$this->deleteForms();
		$this->deletePages();
		$contentPattern = "[caldera_form id=\"%s\"] \n\n %s \n\n <a href=\"%s\">Git Issue %s</a> - <a href=\"%s\">Form Editor</a>";

		/** @var \stdClass $test */
		foreach ( $this->tests as $test ){
			$this->addTest( Factories::testEntity( $test ), $contentPattern );
		}

	}

	/**
	 * Delete all forms
	 */
	public function deleteForms(){
		$forms = \Caldera_Forms_Forms::get_forms();
		if( ! empty( $forms ) ){
			foreach ( $forms as $form ){
				\Caldera_Forms_Forms::delete_form( $form );
			}

		}

	}

	/**
	 * Delete all pages
	 */
	public function deletePages(){
		foreach ( get_posts( array(
			'post_type' => 'page' ,
			'post_status' => 'any'
		) ) as $page ){
			wp_delete_post( $page->ID, true );
		}

	}

	/**
	 * Import form and page from test row
	 *
	 * @param $test
	 * @param $contentPattern
	 */
	protected function addTest( Test $test, $contentPattern )
	{
		$linkPattern = '<div class="ghost-runner-import-report">%s - <a href="%s">Form</a> - <a href="%s">Page</a>';
		$config = json_decode( $test->config, true );
		if( ! $config ){
			return;
		}

		$form = \Caldera_Forms_Forms::create_form( $config );
		if ( is_array( $form ) && isset( $form[ 'ID' ] ) ) {

			$editUrl = calderaGhostRunnerFormUrl( $form[ 'ID' ] );
			$content = sprintf(
				$contentPattern,
				esc_attr( $form[ 'ID' ] ),
				esc_html( $test->description ),
				esc_url( 'https://github.com/calderawp/caldera-forms/issues/' . $test->gitissue ),
				esc_html( $test->gitissue ),
				esc_url( $editUrl )
			);

			$id = wp_insert_post( array(
				'post_title'   => $test->name,
				'post_type'    => 'page',
				'post_content' => $content,
				'post_status'  => 'publish'
			) );

			if ( is_numeric( $id ) ) {
				add_post_meta( $id, 'CGR_gitIssue', $test->gitissue, true );
				add_post_meta( $id, 'CGR_release', $test->release, true );
				add_post_meta( $id, 'CGR_ghostInspectorID', $test->ghostinspectorid, true );
				$post = get_post( $id );

				//@TODO Remove this echo
				printf( $linkPattern, esc_html( $test->name ), esc_url( $editUrl ), esc_url( get_permalink( $post ) ) );
			}

		}
	}

}