// Import Uncanny Owl icon
import {
	UncannyOwlIconColor
} from '../components/icons';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'tincanny/user-quiz-report', {
	title: vc_snc_data_obj.i18n.tinCannyIndividualQuizReportBlockTitle,

	description: vc_snc_data_obj.i18n.tinCannyIndividualQuizReportBlockDescription,

	icon: UncannyOwlIconColor,

	category: 'uncanny-learndash-reporting',

	keywords: [
		'Uncanny Owl',
	],

	supports: {
		html: false
	},

	attributes: {},

	edit({ className, attributes, setAttributes }){
		return (
			<div className={ className }>
				{ vc_snc_data_obj.i18n.tinCannyIndividualQuizReportBlockTitle }
			</div>
		);
	},

	save({ className, attributes }){
		// We're going to render this block using PHP
		// Return null
		return null;
	},
});