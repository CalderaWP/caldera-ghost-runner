<?php

namespace calderawp\object;

/**
 * Class almostStd
 *
 *
 * @package calderawp\object
 */
abstract class almostStd extends jsonAndArrayable {

	/**
	 * almostStd constructor.
	 *
	 * @param \stdClass|null $obj Optional. stdClass object to set properties from
	 */
	public function __construct( \stdClass $obj = null )
	{
		if( null !== $obj ){
			$this->translate( $obj );
		}

	}

	/**
	 * Allow use of named getters
	 *
	 * @param string $name Function name, should be get_$prop()
	 * @param $arguments NOT USED
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		$prop = str_replace( 'get_', '', $name );
		if( property_exists( $this, $prop ) ){
			return $this->$prop;
		}

	}


	/**
	 * Set value of property if it exists
	 *
	 * @param string $prop Name of property
	 * @param mixed $value Value of property
	 *
	 * @throws \Exception If $prop is not string
	 *
	 * @return  bool True.
	 */
	public function __set( $prop, $value )
	{
		if ( is_string( $prop ) ) {
			if( property_exists( $this, $prop ) ){
				$this->$prop = $value;
				return true;
			}

			return false;
		}else{
			throw new \Exception( sprintf( 'Prop passed to almostStd::__set() (as %s) must be string. Type is %s.', get_class( $this ), gettype( $prop ) ) );

		}

	}

	/**
	 * Get value of property if it exists
	 *
	 * @param string $prop Name of property
	 *
	 * @throws \Exception If $prop is not stringh
	 *
	 * @return mixed
	 */
	public function __get( $prop )
	{
		if ( is_string( $prop ) ) {
			if( property_exists( $this, $prop ) ){
				return $this->$prop;
			}
		}

		throw new \Exception( sprintf( 'Prop passed to almostStd::__get() (as %s) must be string. Type is %s.', get_class( $this ), gettype( $prop ) ) );


	}


	/**
	 * Translate from a stdClass object to this object type
	 *
	 * @param \stdClass $obj
	 */
	public function setFromObject( \stdClass $obj )
	{
		$this->translate( $obj );
	}

	/**
	 * @param \stdClass $obj
	 */
	protected function translate( \stdClass $obj )
	{
		foreach ( $obj as $property => $value ) {
			if(  property_exists( $property, $this ) ){
				$this->$property = $value;
			}

		}
	}

}
