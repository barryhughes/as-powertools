<?php
/**
 * Plugin name: Power Tools for Action Scheduler
 * Version:     0.1.0
 */

namespace AS_Powertools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

function init() {
	load();
	plugin()->setup();
}

function load(): void {
	$class_files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( __DIR__ . '/src/php' )
	);

	foreach ( $class_files as $file ) {
		$full_path = $file->getPathname();

		if ( $file->getExtension() === 'php' && ! str_contains( $full_path, '/php/templates/' ) ) {
			require $full_path;
		}
	}
}

function plugin(): Plugin {
	static $plugin;
	return $plugin ?? new Plugin( __DIR__, plugin_dir_url( __FILE__ ) );
}

init();
