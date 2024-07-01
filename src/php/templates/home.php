<div class="wrap as-power-tools">

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Scheduled Actions', 'as-powertools' ); ?></h1>
	<a class="page-title-action" href="?page=action-scheduler"><?php esc_html_e( '⤎ Actions list', 'as-power-tools' ); ?></a>
	<hr class="wp-header-end">

	<ul class="subsubsub">
		<li class="tuning">
			<a href="<?php echo esc_attr( esc_url( admin_url( 'tools.php?page=action-scheduler&powertools=home' ) ) ); ?>" class="current">
				<?php esc_html_e( 'Tuning', 'power-tools' ); ?>
			</a> |
		</li>
		<li class="diagnostics">
			<a href="<?php echo esc_attr( esc_url( admin_url( 'tools.php?page=action-scheduler&powertools=diagnostics' ) ) ); ?>">
				<?php esc_html_e( 'Diagnostics', 'power-tools' ); ?>
			</a>
		</li>
	</ul>

	<form id="as-power-tools-tuning-options" method="post">
		<div class="setting">
			<label for="max-queue-runners">
				<?php esc_html_e( 'Maximum allowed concurrent queue runners', 'as-powertools' ); ?>
			</label>
			<div class="field">
				<?php echo $tunables->generate_input( 'max-queue-runners' ); ?>
				<p><?php esc_html_e( 'Increasing this means more actions can be processed in parallel. However, too high of a value can also be problematic. In most environments, it does not make sense to increase above 5.', 'as-powertools' ); ?></p>
			</div>
		</div>

		<div class="setting">
			<label>
				<?php esc_html_e( 'Batch size by context', 'as-powertools' ); ?>
			</label>
			<div class="field">
				<label class="inlined-label"><?php esc_html_e( 'Default (async HTTP)', 'as-powertools' ); ?></label>
				<?php echo $tunables->generate_input( 'batch-size-default' ); ?>

				<label class="inlined-label"><?php esc_html_e( 'Cron', 'as-powertools' ); ?></label>
				<?php echo $tunables->generate_input( 'batch-size-cron' ); ?>

				<p><?php esc_html_e( 'This determines how many actions each queue runner grabs in a single batch. Decreasing it reduces the potential for long-running actions to block lots of other actions, but increases contention between concurrently running queue runners. Setting a context to zero is the same as disabling it.', 'as-powertools' ); ?></p>
			</div>
		</div>

		<div class="setting">
			<label for="recurring-failure-threshold">
				<?php esc_html_e( 'Failure threshold for recurring actions', 'as-powertools' ); ?>
			</label>
			<div class="field">
				<?php echo $tunables->generate_input( 'recurring-failure-threshold' ); ?>
				<p><?php esc_html_e( 'If the same recurring action fails repeatedly, there is a point at which it should be considered a failure and not be rescheduled.', 'as-powertools' ); ?></p>
			</div>
		</div>

		<div class="setting">
			<label for="retention-period">
				<?php esc_html_e( 'Retention period for old actions', 'as-powertools' ); ?>
			</label>
			<div class="field">
				<?php echo $tunables->generate_input( 'retention-period' ); ?>
				<p><?php esc_html_e( 'Dictates how long completed or cancelled actions should be retained for, before cleaning them up. Reducing this is especially useful for sites processing a large number of actions.', 'as-powertools' ); ?></p>
			</div>
		</div>

		<div class="setting">
			<label for="batch-size-cleanup">
				<?php esc_html_e( 'Clean-up batch size', 'as-powertools' ); ?>
			</label>
			<div class="field">
				<?php echo $tunables->generate_input( 'batch-size-cleanup' ); ?>
				<p><?php esc_html_e( 'Completed or cancelled actions will ultimately be cleaned up (deleted). This controls how many will be cleaned up in a single batch. Increasing this can be useful if you process a very large number of actions.', 'as-powertools' ); ?></p>
			</div>
		</div>

		<div class="setting">
			<?php wp_nonce_field( 'as-powertools-config-home', 'save' ); ?>
			<button class="button-secondary save-button">
				<?php esc_html_e( 'Save', 'as-powertools' ); ?>
			</button>
		</div>
	</form>
</div>