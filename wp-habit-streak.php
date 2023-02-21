<?php
/**
 * Plugin Name:     WP Habit Streak
 * Plugin URI:      https://www.briancoords.com
 * Description:     See your daily blogging streak in WordPress
 * Author:          Brian Coords
 * Author URI:      https://www.briancoords.com
 * Text Domain:     wp-habit-streak
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wp_Habit_Streak
 */

/**
 * Main plugin class
 */
class WP_Habit_Streak {

	/**
	 * The meta key for the streak
	 *
	 * @var string
	 */
	public $meta_key;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->meta_key = 'wp_habit_streak';

		if ( current_user_can( 'edit_posts' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_streak_menu' ), 99 );
		}

		add_action( 'save_post', array( $this, 'save_post_hook' ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );

		add_action( 'init', array( $this, 'register_our_streak_user_meta' ) );

	}


	/**
	 * Hook into the admin bar and display the current streak
	 *
	 * @return void
	 */
	public function add_streak_menu() {
		global $wp_admin_bar;
		$streak = $this->get_streak();
		$wp_admin_bar->add_menu(
			array(
				'id'    => 'wp_habit_streak',
				'title' => 'Streak: ' . $streak,
				'href'  => false,
			)
		);
	}


	/**
	 * Register our user meta
	 */
	public function register_our_streak_user_meta() {
		register_meta(
			'user',
			$this->meta_key,
			array(
				'show_in_rest' => true,
				'type'         => 'integer',
				'description'  => 'The current streak for the user',
			)
		);
	}


	/**
	 * Get the current streak for the current user
	 *
	 * @return int
	 */
	private function get_streak() {
		$streak = get_user_meta( get_current_user_id(), $this->meta_key, true );
		return $streak ? $streak : 0;
	}


	/**
	 * Save the streak for the current user, when a post is saved
	 */
	public function save_post_hook() {
		$streak = $this->calculate_streak( get_current_user_id() );
		update_user_meta( get_current_user_id(), $this->meta_key, $streak );
		clean_user_cache( get_current_user_id() );
	}

	/**
	 * Calculate the streak for a user
	 *
	 * @param int $user_id The user ID.
	 * @return int
	 */
	private function calculate_streak( $user_id ) {

		// Set the streak to 0.
		$streak = 0;

		// Set batch numbers and limit.
		$batch  = 500;
		$offset = 0;
		global $wpdb;

		do {
			$offset++;

			// Get the current users published posts in descending order.
			$posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_date FROM $wpdb->posts WHERE post_author = %d AND post_type = 'post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d OFFSET %d",
					$user_id,
					$batch,
					( $offset - 1 ) * $batch
				)
			);

			if ( ! $posts ) {
				return $streak;
			}

			if ( 1 === $offset ) {

				// Get the first post date.
				$first_post_date = wp_date( 'Y-m-d', strtotime( $posts[0]->post_date ) );

				// If the first post wasn't yesterday and wasn't today, return 0.
				if ( wp_date( 'Y-m-d', strtotime( '-1 day' ) ) !== $first_post_date && wp_date( 'Y-m-d' ) !== $first_post_date ) {
					return $streak;
				}

				// Get the previous post date.
				$previous_post_date = wp_date( 'Y-m-d', strtotime( $posts[0]->post_date ) );

				$streak++;
			}

			foreach ( $posts as $key => $post ) {
				// If this is the first post, skip it.
				if ( 0 === $key && 1 === $offset ) {
					continue;
				}

				// Get the current post date.
				$current_post_date = wp_date( 'Y-m-d', strtotime( $post->post_date ) );

				// If the previous post date is not the day before or same day as the current post date, return the streak.
				if (
					wp_date( 'Y-m-d', strtotime( '-1 day', strtotime( $previous_post_date ) ) ) !== $current_post_date
					&& wp_date( 'Y-m-d', strtotime( 'today', strtotime( $previous_post_date ) ) ) !== $current_post_date
				) {
					return $streak;
				}

				// Increment the streak.
				$streak++;

				// Set the previous post date to the current post date.
				$previous_post_date = $current_post_date;
			}
		} while ( ( $offset * $batch ) === $streak );

		return $streak;
	}

	/**
	 * Enqueue the block editor assets
	 */
	public function enqueue_block_editor_assets() {

		// Get dependencies from index.asset.php.
		$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php' );

		// Enqueue the bundled block JS file.
		wp_enqueue_script(
			'wp-habit-streak',
			plugins_url( 'build/index.js', __FILE__ ),
			$asset_file['dependencies'],
			$asset_file['version']
		);
	}
}

/**
 * Initialize the plugin
 *
 * @return WP_Habit_Streak
 */
function wp_habit_streak() {
	return new WP_Habit_Streak();
}
add_action( 'plugins_loaded', 'wp_habit_streak' );
