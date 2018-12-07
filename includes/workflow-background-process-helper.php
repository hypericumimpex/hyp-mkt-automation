<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) or exit;


/**
 * Class to manage triggers that are initiated in the background.
 *
 * @class Workflow_Background_Process_Helper
 * @since 3.8
 */
class Workflow_Background_Process_Helper {


	/**
	 * Trigger must implement Interfaces\Background_Processed_Trigger
	 *
	 * @param int $workflow_id
	 */
	static function init_process( $workflow_id ) {
		$workflow = Workflow_Factory::get( $workflow_id );

		if ( ! $workflow || ! $workflow->is_active() ) {
			return;
		}

		$trigger = $workflow->get_trigger();

		if ( ! $trigger instanceof Interfaces\Background_Processed_Trigger ) {
			return;
		}

		/** @var Background_Processes\Workflows $process */
		$process = Background_Processes::get('workflows');

		foreach( $trigger->get_background_tasks( $workflow ) as $task ) {
			$process->push_to_queue( $task );
		}

		add_action( 'shutdown', [ __CLASS__, 'start_workflow_background_process' ] );
	}


	/**
	 * Used to start the background processor on shutdown
	 */
	static function start_workflow_background_process() {
		$process = Background_Processes::get('workflows');
		$process->start();
	}


}