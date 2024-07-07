<?php

namespace AS_Powertools;

use ActionScheduler;
use ActionScheduler_AsyncRequest_QueueRunner;
use ActionScheduler_InvalidActionException;
use ActionScheduler_Store;
use ActionScheduler_Versions;
use ReflectionClass;

class Diagnostics {
	private const SPAWN_TEST_KEY = 'as-powertools-async-spawn-test';

	public function setup(): void {
		add_action( 'wp_ajax_as_powertools_diagnostics', [ $this, 'route' ] );
		add_action( 'wp_ajax_as_async_request_queue_runner', [ $this, 'successful_spawn_detector' ], 5 );
	}

	public function route(): void {
		$controller = match( (string) ( $_POST['test'] ?? '' ) ) {
			'parent-plugin'            => [ $this, 'parent_plugin' ],
			'processing-delays'        => [ $this, 'processing_delays' ],
			'processing-delays-severe' => [ $this, 'processing_delays_severe' ],
			'spawn-async'              => [ $this, 'spawn' ],
			default                    => false,

		};

		if ( false === $controller || ! wp_verify_nonce( $_POST['nonce'], 'as-powertools' ) ) {
			wp_send_json_error();
		}

		call_user_func( $controller );
	}

	private function spawn(): void {
		$add_identifying_arg = function ( array $args ): array {
			$args[self::SPAWN_TEST_KEY] = 1;
			return $args;
		};

		// Normally, the dispatch async queue runner request is non-blocking, however this means
		// we won't get any response data.
		$configure_request = function ( array $args ): array {
			$args['blocking'] = true;
			return $args;
		};

		$async = new ActionScheduler_AsyncRequest_QueueRunner( ActionScheduler_Store::instance() );

		add_filter( 'as_async_request_queue_runner_post_args', $configure_request );
		add_filter( 'as_async_request_queue_runner_query_args', $add_identifying_arg );
		$response = $async->dispatch();
		remove_filter( 'as_async_request_queue_runner_post_args', $configure_request );
		remove_filter( 'as_async_request_queue_runner_query_args', $add_identifying_arg );

		$info = json_decode( wp_remote_retrieve_body( $response ) );

		is_object( $info ) && isset( $info->data, $info->data->{self::SPAWN_TEST_KEY} )
			? wp_send_json_success( [ 'message' => esc_html__( 'Successfully spawned an async queue runner (for testing purposes only, no actions were processed).', 'as-powertools' ), 'status' => 'good' ] )
			: wp_send_json_error( [ 'message' => esc_html__( 'Unable to spawn an async queue runner: this mechanism may have been disabled, or may be blocked by the current server configuration.', 'as-powertools' ), 'status' => 'problematic' ] );
	}

	public function successful_spawn_detector(): void {
		if ( isset( $_GET[self::SPAWN_TEST_KEY ] ) ) {
			wp_send_json_success( [ self::SPAWN_TEST_KEY => 1 ] );
		}
	}

	private function processing_delays(): void {
		$this->assess_processing_delays( '-15 minutes', __( '15 minutes', 'as-powertools' ) );
	}

	private function processing_delays_severe(): void {
		$this->assess_processing_delays( '-1 day', __( '1 day', 'as-powertools' ), true );
	}

	/**
	 * Count how many actions are overdue by a certain threshold.
	 * 
	 * @todo consider the strategy pattern so we can (continue to) support arbitrary datastores 
	 *       but also optimize for the most common datastore (DBStore).
	 * 
	 * @todo reconsider how we form the messages, they currently may not translate easily in some
	 *       cases.
	 *
	 * @param string $cutoff_modifier 
	 * @param string $descriptor 
	 *
	 * @return void 
	 */
	private function assess_processing_delays( string $cutoff_modifier, string $descriptor, bool $important = false ): void {
		$counts = ActionScheduler_Store::instance()->action_counts();
		$chunk  = 200;
		$count  = 0;
		$offset = 0;
		$total  = 0;
		$later  = date_create()->modify( $cutoff_modifier );

		do {
			$count = count( (array) ActionScheduler_Store::instance()->query_actions( [
				'date'         => $later,
				'date_compare' => '<=',
				'offset'       => $offset,
				'per_page'     => $chunk,
				'status'       => ActionScheduler_Store::STATUS_PENDING,
			] ) );

			$offset += $chunk;
			$total  += $count;
		} while ( $count === $chunk );

		if ( $count === 0 ) {
			wp_send_json_success( [ 
				'message' => esc_html( sprintf(
					__( 'No actions are currently overdue by more than %s.', 'as-powertools' ),
					$descriptor
				) ),
				'status' => 'good',
			] );
		}
		
		wp_send_json_error( [ 
			'message' => esc_html( sprintf( 
				_n( 
					'%1$d action is overdue by more than %2$s.', 
					'%1$d actions are overdue by more than %2$s.',
					$count,
					'as-powertools' 
				),
				$count,
				$descriptor
			) ),
			'status' => $important ? 'important problematic' : 'problematic',
		 ] );
	}

	private function parent_plugin(): void {
		try {
			$class   = new ReflectionClass( ActionScheduler_InvalidActionException::class );
			$path    = $class->getFileName();
			$version = ActionScheduler_Versions::instance()->latest_version();
			
			if ( str_starts_with( $path, WP_PLUGIN_DIR ) ) {
				$path    = substr( $path, strlen( WP_PLUGIN_DIR ) );
				$parts   = array_filter( explode( DIRECTORY_SEPARATOR, $path ) );
				$plugin  = current( $parts );
				$message = sprintf(
					esc_html__( 'The active version of Action Scheduler (%1$s) is being loaded from "%2$s".', 'as-powertools' ),
					$version,
					$plugin
				);

				if ( $plugin === 'action-scheduler' ) {
					$message .= ' ' . esc_html__( 'This suggests Action Scheduler is installed as a regular, top-level plugin.', 'as-powertools' );
				}
			}
			wp_send_json_success( [
				'message' => $message,
				'status'  => 'good',
			] );
		} finally {
			wp_send_json_success( [
				'message' => esc_html__( 'Could not determine the active parent plugin.', 'as-powertools' ),
				'status'  => 'problematic',
			] );
		}
	}
}
