import { render } from '@wordpress/element';

function HabitStreakSettings() {
	return (
		<div>
			<p>Settings</p>
		</div>
	);
}

window.addEventListener( 'load', () => {
	render( <HabitStreakSettings />, document.getElementById( 'wp-habit-settings-app' ) );
}, false );
