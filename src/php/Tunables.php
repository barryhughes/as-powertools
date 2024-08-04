<?php

namespace AS_Powertools;

use Exception;

/**
 * Provides a means for site operators to tune Action Scheduler and modify various performance
 * characteristics.
 * 
 * The actual work of applying these settings happens in its sibling class, Tuner.
 */
class Tunables {
	/**
	 * The name (key) of the option used to persist Action Scheduler settings.
	 */
	private const CONFIG_KEY = 'as_powertools';

	/**
	 * Definitions for our tunability settings.
	 */
	private const FIELDS = [
		'batch-size-cleanup' => [
			'type'    => 'integer',
			'min'     => 0,
			'max'     => 10000,
			'default' => 100,
		],
		'batch-size-cron' => [
			'type'    => 'integer',
			'min'     => 0,
			'max'     => 10000,
			'default' => 25,
		],
		'batch-size-default' => [
			'type'    => 'integer',
			'min'     => 0,
			'max'     => 10000,
			'default' => 40,
		],
		'max-queue-runners' => [
			'type'    => 'integer',
			'min'     => 0,
			'max'     => 1000,
			'default' => 2,
		],
		'recurring-failure-threshold' => [
			'type'    => 'integer',
			'min'     => 0,
			'max'     => 10000,
			'default' => 5,
		],
		'retention-period' => [
			'type'    => 'timeinterval',
			'default' => '2 days',
		],
	];

	public readonly Tuner $tuner;

	/**
	 * Integrate with WordPress, and with Action Scheduler.
	 */
	public function setup(): void {
		$this->tuner = new Tuner( $this );
		$this->tuner->setup();
		add_action( 'admin_init', [ $this, 'save' ] );
	}

	/**
	 * Takes care of saving changes to our config.
	 */
	public function save() {
		if ( 
			! current_user_can( 'manage_options' ) 
			|| ! wp_verify_nonce( $_POST['save'] ?? '', 'as-powertools-config-home' )
		) {
			return;
		}

		$options = (array) get_option( self::CONFIG_KEY, [] );

		foreach ( $_POST as $key => $value ) {
			try {
				$options[ $key ] = $this->assess( $key, $value );
			} catch ( Exception $e ) {
				continue;
			}
		}

		update_option( self::CONFIG_KEY, $options );
	}

	/**
	 * Assess a setting value. Validate, sanitize, or apply fallback, etc.
	 */
	private function assess( string $key, mixed $value ): mixed {
		if ( ! isset( self::FIELDS[ $key ]['type'] ) ) {
			throw new Exception( 'Unknown property.' );
		}

		if ( $value === null ) {
			return self::FIELDS[ $key ]['default'];
		}

		switch ( self::FIELDS[ $key ]['type'] ) {
			case 'integer':      $value = $this->assess_integral( $value, self::FIELDS[ $key ] ); break;
			case 'timeinterval': $value = $this->assess_timeinterval( $value, self::FIELDS[ $key ] ); break;
		}

		return $value;
	}

	/**
	 * Assess a setting value that is used to store an integer.
	 */
	private function assess_integral( $value, array $conditions ): int {
		try {
			$value = (int) $value;

			if ( isset( $conditions['min'] ) && $value < $conditions['min'] ) {
				$value = $conditions['min'];
			}

			if ( isset( $conditions['max'] ) && $value > $conditions['max'] ) {
				$value = $conditions['max'];
			}
		} catch ( Exception $e ) {
			$value = $conditions['default'] ?? 0;
		}

		return (int) $value;
	}

	/**
	 * Assess a setting value used to store a time interval. This is the format:
	 * 
	 *     "<integer> <period-description>"
	 * 
	 * If just an integer, the period-description is assumed to be seconds.
	 */
	private function assess_timeinterval( $value, array $conditions ): string {
		try {
			if (
				preg_match( '/^([0-9]+)\W*(seconds|minutes|hours|days|weeks|months)$/', $value, $matches ) 
				&& count( $matches ) === 3
			) {
				$integral = (int) $matches[1];
				$units    = $matches[2];
				$value    = "$integral $units";
			} else {
				throw new Exception( 'Unexpected timeinterval format.' );
			}
		} catch ( Exception $e ) {
			$value = $conditions['default'] ?? '3600 seconds';
		}

		return $value;
	}

	/**
	 * Generate an input field for use in the settings UI.
	 */
	public function generate_input( string $key ): string {
		if ( ! isset( self::FIELDS[ $key ] ) ) {
			throw new Exception( 'Oh oh, that definition does not exist.' );
		}

		$options = (array) get_option( self::CONFIG_KEY, [] );

		$attributes   = [];
		$attributes[] = in_array( self::FIELDS[ $key ]['type'], [ 'integer' ] ) ? 'type="number" ' : 'type="text"';
		$attributes[] = isset( self::FIELDS[ $key ]['min'] ) ? 'min="' . esc_attr( self::FIELDS[ $key ]['min'] ) . '"' : '';
		$attributes[] = isset( self::FIELDS[ $key ]['max'] ) ? 'max="' . esc_attr( self::FIELDS[ $key ]['max'] ) . '"' : '';
		$attributes[] = isset( self::FIELDS[ $key ]['default'] ) ? 'value="' . esc_attr( $this->assess( $key, $options[ $key ] ?? null ) ) . '"' : '';
		$attributes[] = 'name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '"';
		$attributes   = join( ' ', array_filter( $attributes ) );

		return "<input $attributes />";
	}

	public function get_value( string $key ): mixed {
		$options = (array) get_option( self::CONFIG_KEY, [] );

		try {
			return $this->assess( $key, $options[ $key ] ?? null );
		} catch ( Exception $e ) {
			return false;
		}
	}
}
