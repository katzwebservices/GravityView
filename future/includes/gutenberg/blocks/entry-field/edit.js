import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Panel, PanelBody, TextControl, Disabled } from '@wordpress/components';

import ViewSelector from 'shared/js/view-selector';
import EntrySelector from 'shared/js/entry-selector';
import PreviewControl from 'shared/js/preview-control';
import PreviewAsShortcodeControl from 'shared/js/preview-as-shortcode-control';
import ServerSideRender from 'shared/js/server-side-render';

import './editor.scss';

export default function Edit( { attributes, setAttributes, name: blockName } ) {
	const {
		viewId,
		entryId,
		fieldId,
		fieldSettingOverrides,
		previewBlock,
		previewAsShortcode,
		showPreviewImage
	} = attributes;

	const previewImage = gkGravityViewBlocks[ blockName ]?.previewImage && <img className="preview-image" src={ gkGravityViewBlocks[ blockName ]?.previewImage } alt={ __( 'Block preview image.', 'gk-gravityview' ) } />;

	if ( previewImage && showPreviewImage ) {
		return previewImage;
	}

	const shouldPreview = ( previewBlock && viewId && entryId && fieldId );

	const fieldSettingOverridesHelpLabel = __( 'These are space-separated overrides for field settings (e.g., title, label, etc.) using the key="value" format. See the [link]GravityView documentation[/link] for more information.', 'gk-gravityview' ).replace( '[link]', '<a href="https://docs.gravitykit.com/article/462-gvfield-embed-gravity-forms-field-values">' ).replace( '[/link]', '</a>' );

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<div className="gk-gravityview-blocks">
					<Panel>
						<PanelBody title={ __( 'Main Settings', 'gk-gravityview' ) } initialOpen={ true }>
							<ViewSelector
								viewId={ viewId }
								onChange={ ( viewId ) => { setAttributes( { viewId, entryId: '' } ); } }
							/>

							<EntrySelector
								disabled={ !viewId }
								entryId={ entryId }
								onChange={ ( entryId ) => { setAttributes( { entryId } ); } }
							>
								<Disabled isDisabled={ !entryId }>
									<TextControl
										className="field-selector"
										label={ __( 'Field ID', 'gk-gravityview' ) }
										placeholder={ __( 'Field ID', 'gk-gravityview' ) }
										value={ fieldId }
										type="number"
										min="0"
										onChange={ ( fieldId ) => setAttributes( { fieldId } ) }
									/>
								</Disabled>

								<Disabled isDisabled={ !entryId || !fieldId }>
									<TextControl
										label={ __( 'Field Setting Overrides', 'gk-gravityview' ) }
										placeholder={ __( 'Field Setting Overrides', 'gk-gravityview' ) }
										help={ <span dangerouslySetInnerHTML={ { __html: fieldSettingOverridesHelpLabel } } /> }
										value={ fieldSettingOverrides }
										onChange={ ( fieldSettingOverrides ) => setAttributes( { fieldSettingOverrides } ) }
									/>
								</Disabled>
							</EntrySelector>

							<PreviewControl
								disabled={ !viewId || !entryId }
								preview={ previewBlock }
								onChange={ ( previewBlock ) => { setAttributes( { previewBlock } ); } }
							/>
						</PanelBody>
					</Panel>
				</div>
			</InspectorControls>

			<PreviewAsShortcodeControl
				previewAsShortcode={ previewAsShortcode }
				disabled={ !previewBlock }
				onChange={ ( previewAsShortcode ) => setAttributes( { previewAsShortcode } ) }
			/>

			{ !shouldPreview && <>
				<div className="block-editor">
					{ previewImage }

					<div>
						<ViewSelector
							viewId={ viewId }
							onChange={ ( viewId ) => { setAttributes( { viewId, entryId: '' } ); } }
						/>

						<EntrySelector
							disabled={ !viewId }
							noButtonGroup={ true }
							entryId={ entryId }
							onChange={ ( entryId ) => { setAttributes( { entryId } ); } }
						/>

						<Disabled isDisabled={ !entryId }>
							<TextControl
								className="field-selector"
								label={ __( 'Field ID', 'gk-gravityview' ) }
								placeholder={ __( 'Field ID', 'gk-gravityview' ) }
								value={ fieldId }
								type="number"
								min="0"
								onChange={ ( fieldId ) => setAttributes( { fieldId } ) }
							/>
						</Disabled>

						<PreviewControl
							disabled={ !viewId || !entryId }
							preview={ previewBlock }
							onChange={ ( previewBlock ) => { setAttributes( { previewBlock } ); } }
						/>
					</div>
				</div>
			</> }

			{ shouldPreview && <>
				<div className="block-preview">
					<Disabled>
						<ServerSideRender
							block={ blockName }
							attributes={ attributes }
						/>
					</Disabled>
				</div>
			</> }
		</div>
	);
}
