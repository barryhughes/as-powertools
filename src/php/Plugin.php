<?php

namespace AS_Powertools;

use ActionScheduler_AdminView;

/**
 * General setup and management, for Power Tools for Action Scheduler.
 */
class Plugin {
	/**
	 * Facilitates various diagnostic tests for Action Scheduler.
	 */
	public readonly Diagnostics $diagnostics;

	/**
	 * Takes care of making it easy to tune various performance characteristics for Action Scheduler.
	 */
	public readonly Tunables $tunables;

		/**
	 * PHP source directory.
	 */
	private string $php;

	/**
	 * Initialize.
	 */
	public function __construct(
		private string $plugin_dir,
		private string $plugin_url,
	) {
		$this->diagnostics = new Diagnostics;		
		$this->php         = $plugin_dir . '/src/php';
		$this->tunables    = new Tunables;

		$this->diagnostics->setup();
		$this->tunables->setup();
	}

	/**
	 * Integrate with WordPress, and with Action Scheduler.
	 */
	public function setup(): void {
		add_action( 'tools_page_action-scheduler', [ $this, 'manage_screen' ], 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'setup_assets' ] );
	}

	/**
	 * When appropriate, renders various user interfaces and sets things up so settings can be persisted, etc.
	 */
	public function manage_screen(): void {
		$pages = [ 'home', 'diagnostics' ];
		$page  = in_array( $_GET['powertools'] ?? '', $pages ) ? $_GET['powertools'] : false;

		if ( false === $page ) {
			return;
		}

		remove_action( 'tools_page_action-scheduler', [ ActionScheduler_AdminView::instance(), 'render_admin_ui' ] );
		call_user_func( [ $this, 'render_' . $page ] );
	}

	/**
	 * Enqueue our settings screens JS and CSS.
	 */
	public function setup_assets(): void {
		wp_enqueue_style( 'as-powertools', $this->plugin_url . 'assets/as-powertools.css' );
		wp_enqueue_script( 'as-powertools', $this->plugin_url . 'assets/as-powertools.js', [ 'wp-i18n' ] );
		wp_localize_script( 'as-powertools', 'asPowerTools', [ 
			'serverUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'as-powertools' ),
		] );
	}

	/**
	 * Render the user interface.
	 */
	public function render_home(): void {
		$tunables = $this->tunables;
		include $this->php . '/templates/home.php';
	}

	public function render_diagnostics(): void {
		$diagnostics = $this->diagnostics;
		include $this->php . '/templates/diagnostics.php';
	}
}