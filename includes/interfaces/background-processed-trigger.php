<?php
// phpcs:ignoreFile

namespace AutomateWoo\Interfaces;

defined( 'ABSPATH' ) or exit;

/**
 * Background Processed Trigger Interface
 *
 * @since 3.8
 */
interface Background_Processed_Trigger {


	/**
	 * Method that the 'workflows' background processor will pass data back to when processing.
	 *
	 * @param \AutomateWoo\Workflow $workflow
	 * @param array $data
	 */
	public function handle_background_task( $workflow, $data );


	/**
	 * Should return an array of tasks to be background processed.
	 *
	 * @param \AutomateWoo\Workflow $workflow
	 * @return array
	 */
	public function get_background_tasks( $workflow );


}
