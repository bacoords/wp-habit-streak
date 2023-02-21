// Hook into the PluginPostPublishPanel component

// Import the dependencies
import { PluginPostPublishPanel } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';


const HabitStreakPostPublishPanel = compose(
	withSelect( select => {
		return {
			postType: select( 'core/editor' ).getCurrentPostType(),
			currentUser: select( 'core' ).getCurrentUser(),
		};
	}
	)
)( ( { postType, currentUser } ) => {
	if ( 'post' !== postType ) {
		return null;
	}

	// Get user meta value 'wp_habit_streak'
	let streak = currentUser.hasOwnProperty( 'meta' ) ? currentUser.meta.wp_habit_streak[0] : 0;

	return (
		<PluginPostPublishPanel>
			<p><strong>WP Habit Streak</strong></p>
			<p>
				{sprintf(
					/* translators: %1$s: Number of streak. */
					__("Congrats! Your current streak is %1$s!", "wp-habit-streak"),
					streak
				)}
			</p>
		</PluginPostPublishPanel>
	);
}
);

registerPlugin( 'habit-streak-post-publish-panel', { render: HabitStreakPostPublishPanel } );

