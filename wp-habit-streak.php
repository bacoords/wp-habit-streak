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
	 * Constructor
	 */
	public function __construct() {
		if ( current_user_can( 'edit_posts' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_streak_menu' ), 99 );
		}
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
	 * Get the current streak for the current user
	 *
	 * @return int
	 */
	private function get_streak() {
		// Add some caching here, and update only on publish hooks or somthing, settings, etc.
		return $this->calculate_streak( get_current_user_id() );
	}

	/**
	 * Calculate the streak for a user
	 *
	 * @param int $user_id The user ID.
	 * @return int
	 */
	private function calculate_streak( $user_id ) {

		// Set batch limit (will be broken into additional function).
		$batch = 100;

		global $wpdb;

		// Get the current users published posts in descending order.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_date FROM $wpdb->posts WHERE post_author = %d AND post_type = 'post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d",
				$user_id,
				$batch
			)
		);

		if ( ! $posts ) {
			return 0;
		}

		// Get the first post date.
		$first_post_date = wp_date( 'Y-m-d', strtotime( $posts[0]->post_date ) );

		// If the first post wasn't yesterday and wasn't today, return 0.
		if ( wp_date( 'Y-m-d', strtotime( '-1 day' ) ) !== $first_post_date && wp_date( 'Y-m-d' ) !== $first_post_date ) {
			return 0;
		}

		// Loop through the posts until we find a gap.
		$streak = 1;
		foreach ( $posts as $key => $post ) {
			// If this is the first post, skip it.
			if ( 0 === $key ) {
				continue;
			}

			// Get the previous post date.
			$previous_post_date = wp_date( 'Y-m-d', strtotime( $posts[ $key - 1 ]->post_date ) );

			// Get the current post date.
			$current_post_date = wp_date( 'Y-m-d', strtotime( $post->post_date ) );

			// If the previous post date is not the day before the current post date, return the streak.
			if ( wp_date( 'Y-m-d', strtotime( '-1 day', strtotime( $previous_post_date ) ) ) !== $current_post_date ) {
				return $streak;
			}

			// Increment the streak.
			$streak++;
		}

		return $streak;
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
