<div class="wrap as-power-tools">

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Scheduled Actions', 'as-powertools' ); ?></h1>
	<a class="page-title-action" href="?page=action-scheduler"><?php esc_html_e( '⤎ Actions list', 'as-power-tools' ); ?></a>
	<hr class="wp-header-end">

	<ul class="subsubsub">
		<li class="tuning">
			<a href="<?php echo esc_attr( esc_url( admin_url( 'tools.php?page=action-scheduler&powertools=home' ) ) ); ?>">
				<?php esc_html_e( 'Tuning', 'power-tools' ); ?>
			</a> |
		</li>
		<li class="diagnostics">
			<a href="<?php echo esc_attr( esc_url( admin_url( 'tools.php?page=action-scheduler&powertools=diagnostics' ) ) ); ?>" class="current">
				<?php esc_html_e( 'Diagnostics', 'power-tools' ); ?>
			</a>
		</li>
	</ul>

	<form id="as-power-tools-diagnostics" method="post">
		<div class="setting">
			<label for="async-queue-runner">
				<?php esc_html_e( 'General health', 'as-powertools' ); ?>
			</label>
			<div class="field">
				<div class="diagnostic-assessment" data-test="spawn-async">
					<div class="indicator undetermined"></div>
					<div class="description"><?php esc_html_e( 'Check if async queue runners can be spawned&hellip;', 'as-powertools' ); ?></div>
				</div>

				<div class="diagnostic-assessment" data-test="processing-delays">
					<div class="indicator undetermined"></div>
					<div class="description"><?php esc_html_e( 'Look for processing delays&hellip;', 'as-powertools' ); ?></div>
				</div>

				<div class="diagnostic-assessment" data-test="processing-delays-severe">
					<div class="indicator undetermined"></div>
					<div class="description"><?php esc_html_e( 'Look for (severe) processing delays&hellip;', 'as-powertools' ); ?></div>
				</div>

				<p><?php esc_html_e( 'In most cases, async HTTP queue runners are the primary way of processing the queue of actions.', 'as-powertools' ); ?></p>
			</div>
		</div>

		<input type="hidden" name="safety-check" value="<?php echo esc_attr( wp_create_nonce( 'as-power-tool-diagnostics' ) ); ?>" />

		<div class="setting">
			<?php wp_nonce_field( 'as-powertools-config-diagnostics', 'save' ); ?>
			<button class="button-secondary save-button">
				<?php esc_html_e( 'Refresh', 'as-powertools' ); ?>
			</button>
		</div>
	</form>
</div>