<?php

namespace AS_Powertools;

/**
 * Handles the actual tuning of various Action Scheduler properties.
 * 
 * @todo find a better way of killing async and cron when batch sizes are set to 0.
 */
class Tuner {
	public function __construct(
		private Tunables $tunables,
	) {}

	public function setup(): void {
		add_filter( 'action_scheduler_cleanup_batch_size', fn () => $this->get_simple_setting_value( 'batch-size-cleanup' ), 200 );
		add_filter( 'action_scheduler_queue_runner_batch_size', [ $this, 'control_batch_size' ] );
		add_filter( 'action_scheduler_queue_runner_concurrent_batches', fn () => $this->get_simple_setting_value( 'max-queue-runners' ), 200 );
		add_filter( 'action_scheduler_recurring_action_failure_threshold', fn () => $this->get_simple_setting_value( 'recurring-failure-threshold' ), 200 );
		add_filter( 'action_scheduler_retention_period', fn () => $this->get_simple_setting_value( 'retention-period' ), 200 );
	}

	public function control_batch_size(): int {
		return defined( 'DOING_CRON' ) && DOING_CRON
			? $this->get_simple_setting_value( 'batch-size-cron' )
			: $this->get_simple_setting_value( 'batch-size-default' );
	}

	private function get_simple_setting_value( string $key ): mixed {
		$value = $this->tunables->get_value( $key );
		return $value === false ? null : $value;
	}
}