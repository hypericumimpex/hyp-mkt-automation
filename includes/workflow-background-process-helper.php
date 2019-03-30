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
	 * Trigger must extend Trigger_Background_Processed_Abstract
	 *
	 * @param int $workflow_id
	 * @param int $offset The DB query offset for the trigger.
	 */
	static function init_process( $workflow_id, $offset = 0 ) {
		$workflow = Workflow_Factory::get( $workflow_id );

		if ( ! $workflow || ! $workflow->is_active() ) {
			return;
		}

		$trigger = $workflow->get_trigger();

		if ( ! $trigger instanceof Trigger_Background_Processed_Abstract ) {
			return;
		}

		$limit = self::get_background_process_batch_size( $workflow );

		/** @var Background_Processes\Workflows $process */
		$process = Background_Processes::get('workflows');
		$tasks = $trigger->get_background_tasks( $workflow, $limit, $offset );

		foreach( $tasks as $task ) {
			$process->push_to_queue( $task );
		}

		// If the workflow has tasks, schedule another batch
		if ( ! empty( $tasks ) ) {
			self::schedule_next_batch( $workflow_id, $offset + $limit );
		}

		add_action( 'shutdown', [ __CLASS__, 'start_workflow_background_process' ] );
	}


	/**
	 * Schedules a follow up event, one minute from now that will init another batch of tasks.
	 *
	 * @param int $workflow_id
	 * @param int $new_offset
	 */
	private static function schedule_next_batch( $workflow_id, $new_offset ) {
		if ( ! $new_offset ) {
			// offset should be greater than 0
			return;
		}

		Events::schedule_async_event( 'automatewoo/custom_time_of_day_workflow', [
			$workflow_id, $new_offset
		], true );
	}


	/**
	 * Used to start the background processor on shutdown
	 */
	static function start_workflow_background_process() {
		$process = Background_Processes::get('workflows');
		$process->start();
	}


	/**
	 * Get the batch size for workflows that use the background processor.
	 *
	 * This is the max number of items that will be passed to the background processor at once.
	 *
	 * @since 4.5
	 *
	 * @param Workflow $workflow
	 *
	 * @return int
	 */
	private static function get_background_process_batch_size( $workflow ) {
		return apply_filters( 'automatewoo/workflows/background_process_batch_size', 50, $workflow );
	}


}