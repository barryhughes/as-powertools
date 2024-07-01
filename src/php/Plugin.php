<?php

namespace AS_Powertools;

use ActionScheduler_AdminView;

/**
 * General setup and management, for Power Tools for Action Scheduler.
 */
class Plugin {
	/**
	 * PHP source directory.
	 */
	private string $php;

	/**
	 * Takes care of making it easy to tune various performance characteristics for Action Scheduler.
	 */
	public readonly Tunables $tunables;

	/**
	 * Initialize.
	 */
	public function __construct(
		private string $plugin_dir,
		private string $plugin_url,
	) {
		$this->php      = $plugin_dir . '/src/php';
		$this->tunables = new Tunables;
		$this->tunables->setup();
	}

	/**
	 * Integrate with WordPress, and with Action Scheduler.
	 */
	public function setup(): void {
		add_action( 'tools_page_action-scheduler', [ $this, 'manage_screen' ], 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'setup_assets' ] );
		add_action( 'admin_init', [ $this, 'save' ] );
	}

	/**
	 * When appropriate, renders various user interfaces and sets things up so settings can be persisted, etc.
	 */
	public function manage_screen(): void {
		if ( empty( $_GET['powertools'] ) ) {
			return;
		}

		remove_action( 'tools_page_action-scheduler', [ ActionScheduler_AdminView::instance(), 'render_admin_ui' ] );
		$this->save();
		$this->render_ui();
	}

	/**
	 * Enqueue our settings screens JS and CSS.
	 */
	public function setup_assets(): void {
		wp_enqueue_script( 'as-powertools', $this->plugin_url . 'assets/as-powertools.js' );
		wp_enqueue_style( 'as-powertools', $this->plugin_url . 'assets/as-powertools.css' );
	}

	/**
	 * Render the user interface.
	 */
	public function render_ui(): void {
		$tunables = $this->tunables;
		include $this->php . '/templates/home.php';
	}

	/**
	 * Save settings, if appropriate.
	 */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( wp_verify_nonce( $_POST['save'] ?? '', 'as-powertools-config-home' ) ) {
			$this->tunables->save( $_POST );
		}
	}
}