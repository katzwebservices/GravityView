import { __, _x } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { RawHTML } from '@wordpress/element';
import { BaseControl, Panel, PanelBody, ButtonGroup, Button, TextControl, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import ViewSelector from 'shared/js/view-selector';
import './editor.scss';

export default function Edit( { attributes, setAttributes, name: blockName } ) {
	const {
		view_id: viewId,
		entry_id: entryId,
		field_id: fieldId,
		field_setting_overrides: fieldSettingOverrides,
		blockPreview
	} = attributes;

	const shouldPreview = ( viewId && entryId && fieldId );

	const selectFromBlockControlsLabel = _x( 'Please select [control] from the block controls.', '[control] placeholder should not be translated and will be replaced with "an Entry ID" or "a View ID" text.', 'gk-gravityview' );

	const fieldSettingOverridesHelpLabel = __( 'These are space-separated overrides for field settings (e.g., title, label, etc.) using the key="value" format. See the [link]GravityView documentation[/link] for more information.', 'gk-gravityview' ).replace( '[link]', '<a href="https://docs.gravitykit.com/article/462-gvfield-embed-gravity-forms-field-values">' ).replace( '[/link]', '</a>' );

	const previewImage = gkGravityViewBlocks[ blockName ]?.previewImage;

	const showBlockPreviewImage = () => <img className="gk-gravityview-block block-preview" src={ previewImage } alt={ __( 'Block preview image.', 'gk-gravityview' ) } />;

	if ( blockPreview && previewImage ) {
		return showBlockPreviewImage();
	}

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<Panel>
					<PanelBody title={ __( 'Main Settings', 'gk-gravityview' ) } initialOpen={ true }>
						<ViewSelector
							viewId={ viewId }
							onChange={ ( view_id ) => { setAttributes( { view_id, entry_id: '' } ); } }
						/>

						{ viewId && <>
							<BaseControl label={ __( 'Entry Type', 'gk-gravityview' ) }>
								<ButtonGroup className="gk-gravityview-block btn-group-triple">
									<Button
										isPrimary={ entryId !== 'first' && entryId !== 'last' }
										onClick={ () => setAttributes( { entry_id: '' } ) }
									>
										{ __( 'Entry ID', 'gk-gravityview' ) }
									</Button>

									<Button
										isPrimary={ entryId === 'first' }
										onClick={ () => setAttributes( { entry_id: 'first' } ) }
									>
										{ __( 'First', 'gk-gravityview' ) }
									</Button>

									<Button
										isPrimary={ entryId === 'last' }
										onClick={ () => setAttributes( { entry_id: 'last' } ) }
									>
										{ __( 'Last', 'gk-gravityview' ) }
									</Button>
								</ButtonGroup>

								{ entryId !== 'first' && entryId !== 'last' && <>
									<TextControl
										label={ __( 'Entry ID', 'gk-gravityview' ) }
										placeholder={ __( 'Entry ID', 'gk-gravityview' ) }
										value={ entryId }
										type="number"
										min="1"
										onChange={ ( entry_id ) => setAttributes( { entry_id } ) }
									/>
								</> }

								{ entryId && <>
									<TextControl
										label={ __( 'Field ID', 'gk-gravityview' ) }
										placeholder={ __( 'Field ID', 'gk-gravityview' ) }
										value={ fieldId }
										type="number"
										min="1"
										onChange={ ( field_id ) => setAttributes( { field_id } ) }
									/>

									<TextControl
										label={ __( 'Field Setting Overrides', 'gk-gravityview' ) }
										placeholder={ __( 'Field Setting Overrides', 'gk-gravityview' ) }
										help={ RawHTML( { children: fieldSettingOverridesHelpLabel } ) }
										value={ fieldSettingOverrides }
										onChange={ ( field_setting_overrides ) => setAttributes( { field_setting_overrides } ) }
									/>
								</> }
							</BaseControl>
						</> }
					</PanelBody>
				</Panel>
			</InspectorControls>

			{ !shouldPreview && <>
				<div className="gk-gravityview-block shortcode-preview">
					{ previewImage && showBlockPreviewImage() }

					<div className="field-container">
						{ !viewId && <p>{ selectFromBlockControlsLabel.replace( '[control]', __( 'a View ID', 'gk-gravityview' ) ) }</p> }
						{ ( viewId && !entryId ) && <p>{ selectFromBlockControlsLabel.replace( '[control]', __( 'an Entry ID', 'gk-gravityview' ) ) }</p> }
						{ ( viewId && entryId && !fieldId ) && <p>{ selectFromBlockControlsLabel.replace( '[control]', __( 'a Field ID', 'gk-gravityview' ) ) }</p> }
					</div>
				</div>
			</> }

			{ shouldPreview && <>
				<ServerSideRender
					LoadingResponsePlaceholder={ () => <>
						{ __( 'Rendering preview...', 'gk-gravityview' ) }
						<Spinner />
					</> }
					block={ blockName }
					attributes={ attributes }
				/>
			</> }
		</div>
	);
}
