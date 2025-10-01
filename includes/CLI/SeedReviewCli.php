<?php
/**
 * WP-CLI commands for seeding sample reviews.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\CLI;

/**
 * WP-CLI command class for seeding sample reviews.
 */
class SeedReviewCli {

	/**
	 * Sample review seeder dependency.
	 *
	 * @var \WcAiReviewResponder\CLI\SampleReviewSeeder
	 */
	private $seeder;

	/**
	 * Constructor.
	 *
	 * @param SampleReviewSeeder $seeder Sample review seeder.
	 */
	public function __construct( SampleReviewSeeder $seeder ) {
		$this->seeder = $seeder;
	}

	/**
	 * Seed the database with sample reviews for testing.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Force seeding even if reviews already exist.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ai-review-seed seed
	 *     wp ai-review-seed seed --force
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function seed( $args, $assoc_args ) {
		// Parameter is part of the WP-CLI signature but unused here.
		unset( $args );

		$force = isset( $assoc_args['force'] ) && $assoc_args['force'];

		\WP_CLI::log( 'Starting database seeding for sample reviews...' );

		$result = $this->seeder->seed( $force );

		if ( $result['success'] ) {
			\WP_CLI::success( sprintf( 'Successfully created %d products and %d reviews.', $result['products_created'], $result['reviews_created'] ) );

			\WP_CLI::log( '' );
			\WP_CLI::log( 'To test AI responses, use:' );
			foreach ( $result['reviews'] as $review ) {
				\WP_CLI::log( sprintf( 'wp ai-review test %d  # %s template', $review['review_id'], $review['template_type'] ) );
			}
		} else {
			\WP_CLI::error( $result['message'] );
		}
	}
}
