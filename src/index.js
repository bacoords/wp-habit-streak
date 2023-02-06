// Hook into the PluginPostPublishPanel component

// Import the dependencies
import { PluginPostPublishPanel } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';


const MyPostPublishPanel = compose(
	withSelect( select => {
		return {
			postType: select( 'core/editor' ).getCurrentPostType(),
		};
	}
	)
)( ( { postType } ) => {
	if ( 'post' !== postType ) {
		return null;
	}
	return (
		<PluginPostPublishPanel>
			<p>My Post Publish Panel</p>
		</PluginPostPublishPanel>
	);
}
);

registerPlugin( 'my-post-publish-panel', { render: MyPostPublishPanel } );

