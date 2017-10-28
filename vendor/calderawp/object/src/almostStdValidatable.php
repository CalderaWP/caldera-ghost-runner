<?php


namespace calderawp\object;

/**
 * Class almostStdValidatable
 *
 * Adds a validation callback to get/set
 *
 * @package calderawp\object
 */
abstract class almostStdValidatable extends almostStd {


	/**
	 * Set allowed properties with possible validation callback
	 *
	 * @param string $property Name of property
	 * @param mixed $value Property value
	 *
	 * @return bool|mixed
	 */
	public function __set( $property, $value )
	{
		if ( property_exists( $this, $property ) ) {
			$setter = $property . '_set';
			if ( method_exists( $this, $setter ) ) {
				return call_user_func( array( $this, $setter ), $value );
			} else {
				$this->$property = $value;

				return true;
			}
		}

	}

	/**
	 * Get allowed property value with possible validation
	 *
	 * @param string $property Name of property
	 *
	 * @return mixed|null|void
	 */
	public function __get( $property )
	{
		if ( property_exists( $this, $property ) ) {
			$getter = $property . '_get';
			if( method_exists( $this, $getter  ) ){
				return call_user_func( array( $this, $getter ) );
			}
			
			if ( property_exists( $this, $property ) ) {
				return $this->apply_filter( $property, $this->$property );
			} else {
				return null;
			}

		}

	}

	/**
	 * Filter value if is WordPress (else, pass through)
	 *
	 * Called whenever property is accessed via __get() and apply_filters() function exists because is WordPress
	 *
	 * @param string $property Property name
	 * @param mixed $value Property value
	 *
	 * @return mixed|void
	 */
	public function apply_filter( $property, $value )
	{
		if ( function_exists( 'apply_filters' ) ) {
			$prefix      = $this->get_prefix();
			$filter_name = $prefix . '_' . $property;

			/**
			 * Filter value before returning
			 *
			 * @since 1.4.0
			 *
			 * @param mixed $value Property value
			 * @param almostStdValidatable $obj Current class object
			 * @param string $class Name of class
			 */
			return apply_filters( $filter_name, $value, $this, get_class( $this ) );
		}

		return $value;

	}

	/**
	 * Get prefix to use in parents.
	 *
	 * Used to form filter for getters. Better to override and hardcode.
	 *
	 * @return string
	 */
	protected function get_prefix()
	{
		$class = explode( '_', get_class( $this ) );
		end( $class );
		return  key( $class );

	}

}
