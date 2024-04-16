<?php
/**
 * GravityView default templates and generic template class
 *
 * @file      register-default-templates.php
 * @since     2.10
 * @license   GPL2+
 * @author    GravityKit <hello@gravitykit.com>
 * @link      http://www.gravitykit.com
 * @copyright Copyright 2015, Katz Web Services, Inc.
 *
 * @package   GravityView
 */

// Load default templates
add_action( 'init', 'gravityview_register_default_templates', 11 );

/**
 * Registers the default templates
 *
 * @return void
 */
function gravityview_register_default_templates() {
	/** @define "GRAVITYVIEW_DIR" "../../" */

	// The abstract class required by all template files.
	require_once GRAVITYVIEW_DIR . 'includes/class-gravityview-template.php';

	$path = GRAVITYVIEW_DIR . 'includes/presets/';
	include_once $path . 'default-table/class-gravityview-default-template-table.php';
	include_once $path . 'default-list/class-gravityview-default-template-list.php';
	include_once $path . 'default-edit/class-gravityview-default-template-edit.php';
	include_once $path . 'business-listings/class-gravityview-preset-business-listings.php';
	include_once $path . 'business-data/class-gravityview-preset-business-data.php';
	include_once $path . 'profiles/class-gravityview-preset-profiles.php';
	include_once $path . 'staff-profiles/class-gravityview-preset-staff-profiles.php';
	include_once $path . 'website-showcase/class-gravityview-preset-website-showcase.php';
	include_once $path . 'issue-tracker/class-gravityview-preset-issue-tracker.php';
	include_once $path . 'resume-board/class-gravityview-preset-resume-board.php';
	include_once $path . 'job-board/class-gravityview-preset-job-board.php';
	include_once $path . 'event-listings/class-gravityview-preset-event-listings.php';
}


// Register after other templates
add_action( 'init', 'gravityview_register_placeholder_templates', 2000 );

/**
 * Register the placeholder templates to make it clear what layouts are available
 *
 * @since 2.10
 *
 * @return void
 */
function gravityview_register_placeholder_templates() {

	require_once GRAVITYVIEW_DIR . 'includes/class-gravityview-placeholder-template.php';

	$placeholders = array(
		'GravityView_DataTables_Template'       => array(
			'slug'        => 'dt_placeholder',
			'template_id' => 'datatables_table',
			'download_id' => 268,
			'label'       => __( 'DataTables Table', 'gv-datatables', 'gk-gravityview' ),
			'description' => __( 'Display items in a dynamic table powered by DataTables.', 'gk-gravityview' ),
			'logo'        => plugins_url( 'assets/images/templates/logo-datatables.png', GRAVITYVIEW_FILE ),
			'icon'        => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDEiIGhlaWdodD0iMzYiIHZpZXdCb3g9IjAgMCA0MSAzNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0zIDM1SDI4LjQyNTNDMjkuNTI5OSAzNSAzMC40MjUzIDM0LjEwNDYgMzAuNDI1MyAzM1Y3LjU3NDcxQzMwLjQyNTMgNi40NzAxNCAyOS41Mjk5IDUuNTc0NzEgMjguNDI1MyA1LjU3NDcxSDNDMS44OTU0MyA1LjU3NDcxIDEgNi40NzAxNCAxIDcuNTc0NzFWMzNDMSAzNC4xMDQ2IDEuODk1NDMgMzUgMyAzNVoiIGZpbGw9IndoaXRlIiBzdHJva2U9IiMyQzMzMzgiIHN0cm9rZS13aWR0aD0iMS41IiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogICAgPHBhdGggZD0iTTMwLjQyNTMgMjEuNzU4NlYzM0MzMC40MjUzIDM0LjEwNDYgMjkuNTI5OSAzNSAyOC40MjUzIDM1SDNDMS44OTU0MyAzNSAxIDM0LjEwNDYgMSAzM1Y3LjU3NDcxQzEgNi40NzAxNCAxLjg5NTQzIDUuNTc0NzEgMyA1LjU3NDcxSDE5Ljc1ODYiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNNS40MTM1NyAyMS43NTg1SDEzLjUwNTUiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMTcuOTE5NCAyMS43NTg1SDI2LjAxMTQiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNNS40MTM1NyAzMC41ODYySDEzLjUwNTUiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMTcuOTE5NCAzMC41ODYySDI2LjAxMTQiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNNS40MTM1NyAyNi4xNzI0SDEzLjUwNTUiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMTcuOTE5NCAyNi4xNzI0SDI2LjAxMTQiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNNi44ODUyNSAxMS40NTk3SDE5LjM5MSIgc3Ryb2tlPSIjMkMzMzM4IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW1pdGVybGltaXQ9IjEwIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik0xLjM2NzY4IDE2LjYwOTFIMjAuNDk0MSIgc3Ryb2tlPSIjMkMzMzM4IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW1pdGVybGltaXQ9IjEwIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik0zMC4wNTc1IDE5LjIxMjZDMzQuOTQ4NyAxOS4yMTI2IDM4LjkxMzggMTUuMjQ3NSAzOC45MTM4IDEwLjM1NjNDMzguOTEzOCA1LjQ2NTExIDM0Ljk0ODcgMS41IDMwLjA1NzUgMS41QzI1LjE2NjMgMS41IDIxLjIwMTIgNS40NjUxMSAyMS4yMDEyIDEwLjM1NjNDMjEuMjAxMiAxNS4yNDc1IDI1LjE2NjMgMTkuMjEyNiAzMC4wNTc1IDE5LjIxMjZaIiBmaWxsPSJ3aGl0ZSIgc3Ryb2tlPSIjRjNGNEY1IiBzdHJva2Utd2lkdGg9IjMiLz4KICAgIDxwYXRoIGQ9Ik0zNi42NjM4IDEwLjM1NjNDMzYuNjYzOCAxNC4wMDQ5IDMzLjcwNjEgMTYuOTYyNiAzMC4wNTc1IDE2Ljk2MjZDMjYuNDA4OSAxNi45NjI2IDIzLjQ1MTIgMTQuMDA0OSAyMy40NTEyIDEwLjM1NjNDMjMuNDUxMiA2LjcwNzc1IDI2LjQwODkgMy43NSAzMC4wNTc1IDMuNzVDMzMuNzA2MSAzLjc1IDM2LjY2MzggNi43MDc3NSAzNi42NjM4IDEwLjM1NjNaIiBmaWxsPSJ3aGl0ZSIgc3Ryb2tlPSIjMkMzMzM4IiBzdHJva2Utd2lkdGg9IjEuNSIvPgogICAgPHBhdGggZD0iTTI0LjU0IDEwLjM1NjNIMjcuODUwNEwyOS4zMjE2IDguODg1MDFMMzEuNTI4NSAxMS44Mjc1TDMyLjk5OTggMTAuMzU2M0gzNS4yMDY3IiBzdHJva2U9IiMyQzMzMzgiIHN0cm9rZS13aWR0aD0iMS41IiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+Cg==',
			'buy_source'  => 'https://www.gravitykit.com/pricing/?utm_source=plugin&utm_medium=buy_now&utm_campaign=view_type&utm_term=datatables',
			'preview'     => 'https://try.gravitykit.com/demo/view/datatables/?utm_source=plugin&utm_medium=try_demo&utm_campaign=view_type&utm_term=datatables',
			'license'     => esc_html__( 'All Access', 'gk-gravityview' ),
			'price_id'    => 2,
			'textdomain'  => 'gv-datatables|gk-datatables',
		),
		'GravityView_Maps_Template_Map_Default' => array(
			'slug'        => 'map_placeholder',
			'template_id' => 'map',
			'download_id' => 27,
			'label'       => __( 'Map', 'gravityview-maps', 'gk-gravityview' ),
			'description' => __( 'Display entries on a map.', 'gk-gravityview' ),
			'logo'        => plugins_url( 'assets/images/templates/default-map.png', GRAVITYVIEW_FILE ),
			'icon'        => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzYiIGhlaWdodD0iMzQiIHZpZXdCb3g9IjAgMCAzNiAzNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0yOS45NTI0IDUuNTcxNTNIMzEuNDc2MkMzMi4yODQ1IDUuNTcxNTMgMzMuMDU5NiA1Ljg5MjYyIDMzLjYzMTIgNi40NjQxNkMzNC4yMDI3IDcuMDM1NyAzNC41MjM4IDcuODEwODcgMzQuNTIzOCA4LjYxOTE1VjI5Ljk1MjVDMzQuNTIzOCAzMC43NjA4IDM0LjIwMjcgMzEuNTM1OSAzMy42MzEyIDMyLjEwNzVDMzMuMDU5NiAzMi42NzkgMzIuMjg0NSAzMy4wMDAxIDMxLjQ3NjIgMzMuMDAwMUg0LjA0NzYyQzMuMjM5MzQgMzMuMDAwMSAyLjQ2NDE3IDMyLjY3OSAxLjg5MjYzIDMyLjEwNzVDMS4zMjEwOSAzMS41MzU5IDEgMzAuNzYwOCAxIDI5Ljk1MjVWOC42MTkxNUMxIDcuODEwODcgMS4zMjEwOSA3LjAzNTcgMS44OTI2MyA2LjQ2NDE2QzIuNDY0MTcgNS44OTI2MiAzLjIzOTM0IDUuNTcxNTMgNC4wNDc2MiA1LjU3MTUzSDYuMzMzMzMiIGZpbGw9IndoaXRlIi8+CiAgICA8cGF0aCBkPSJNMjkuOTUyNCA1LjU3MTUzSDMxLjQ3NjJDMzIuMjg0NSA1LjU3MTUzIDMzLjA1OTYgNS44OTI2MiAzMy42MzEyIDYuNDY0MTZDMzQuMjAyNyA3LjAzNTcgMzQuNTIzOCA3LjgxMDg3IDM0LjUyMzggOC42MTkxNVYyOS45NTI1QzM0LjUyMzggMzAuNzYwOCAzNC4yMDI3IDMxLjUzNTkgMzMuNjMxMiAzMi4xMDc1QzMzLjA1OTYgMzIuNjc5IDMyLjI4NDUgMzMuMDAwMSAzMS40NzYyIDMzLjAwMDFINC4wNDc2MkMzLjIzOTM0IDMzLjAwMDEgMi40NjQxNyAzMi42NzkgMS44OTI2MyAzMi4xMDc1QzEuMzIxMDkgMzEuNTM1OSAxIDMwLjc2MDggMSAyOS45NTI1VjguNjE5MTVDMSA3LjgxMDg3IDEuMzIxMDkgNy4wMzU3IDEuODkyNjMgNi40NjQxNkMyLjQ2NDE3IDUuODkyNjIgMy4yMzkzNCA1LjU3MTUzIDQuMDQ3NjIgNS41NzE1M0g2LjMzMzMzIiBzdHJva2U9IiMyQzMzMzgiIHN0cm9rZS13aWR0aD0iMS41IiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogICAgPHBhdGggZD0iTTE3Ljc2MTcgMjguNDI4NVYyNS4zODA5IiBzdHJva2U9IiMyQzMzMzgiIHN0cm9rZS13aWR0aD0iMS41IiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogICAgPHBhdGggZD0iTTI5Ljk1MjIgMTAuOTA0OFYyOC40Mjg2SDUuNTcxMjlWMTAuOTA0OCIgc3Ryb2tlPSIjMkMzMzM4IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW1pdGVybGltaXQ9IjEwIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik01LjU3MTI5IDI4LjQyODdMMTQuNzg1IDE3LjYzNDgiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMjkuOTUyNSAyOC40Mjg3TDIwLjczODggMTcuNjM0OCIgc3Ryb2tlPSIjMkMzMzM4IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW1pdGVybGltaXQ9IjEwIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik0yNS4zODEyIDguNjE5MDVDMjUuMzgxMiAxMy4zMjE1IDE3Ljc2MjEgMjEgMTcuNzYyMSAyMUMxNy43NjIxIDIxIDEwLjE0MzEgMTMuMzIxNSAxMC4xNDMxIDguNjE5MDVDMTAuMTQzMSA2LjU5ODM1IDEwLjk0NTggNC42NjA0MiAxMi4zNzQ2IDMuMjMxNTdDMTMuODAzNSAxLjgwMjcyIDE1Ljc0MTQgMSAxNy43NjIxIDFDMTkuNzgyOCAxIDIxLjcyMDcgMS44MDI3MiAyMy4xNDk2IDMuMjMxNTdDMjQuNTc4NCA0LjY2MDQyIDI1LjM4MTIgNi41OTgzNSAyNS4zODEyIDguNjE5MDVaIiBmaWxsPSJ3aGl0ZSIgc3Ryb2tlPSIjMkMzMzM4IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLW1pdGVybGltaXQ9IjEwIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik0xNy43NjIxIDEwLjE0MjhDMTguNjAzNyAxMC4xNDI4IDE5LjI4NTkgOS40NjA2IDE5LjI4NTkgOC42MTkwMkMxOS4yODU5IDcuNzc3NDUgMTguNjAzNyA3LjA5NTIxIDE3Ljc2MjEgNy4wOTUyMUMxNi45MjA1IDcuMDk1MjEgMTYuMjM4MyA3Ljc3NzQ1IDE2LjIzODMgOC42MTkwMkMxNi4yMzgzIDkuNDYwNiAxNi45MjA1IDEwLjE0MjggMTcuNzYyMSAxMC4xNDI4WiIgZmlsbD0iIzJDMzMzOCIvPgo8L3N2Zz4K',
			'buy_source'  => 'https://www.gravitykit.com/pricing/?utm_source=plugin&utm_medium=buy_now&utm_campaign=view_type&utm_term=map',
			'preview'     => 'https://try.gravitykit.com/demo/view/map/?utm_source=plugin&utm_medium=try_demo&utm_campaign=view_type&utm_term=map',
			'license'     => esc_html__( 'All Access', 'gk-gravityview' ),
			'price_id'    => 2,
			'textdomain'  => 'gravityview-maps|gk-gravitymaps',
		),
		'GravityView_DIY_Template'              => array(
			'slug'        => 'diy_placeholder',
			'template_id' => 'diy',
			'download_id' => 550152,
			'label'       => _x( 'DIY', 'DIY means "Do It Yourself"', 'gk-gravityview' ),
			'description' => esc_html__( 'A flexible, powerful layout for designers & developers.', 'gk-gravityview' ),
			'buy_source'  => 'https://www.gravitykit.com/pricing/?utm_source=plugin&utm_medium=buy_now&utm_campaign=view_type&utm_term=diy',
			'logo'        => plugins_url( 'assets/images/templates/logo-diy.png', GRAVITYVIEW_FILE ),
			'icon'        => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzQiIGhlaWdodD0iMzQiIHZpZXdCb3g9IjAgMCAzNCAzNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0yOS4wNTQxIDMuOTA5NjdIMzAuOTk5NUMzMi4xMDQgMy45MDk2NyAzMi45OTk1IDQuODA1MSAzMi45OTk1IDUuOTA5NjdWMzEuMDAwMUMzMi45OTk1IDMyLjEwNDYgMzIuMTA0IDMzLjAwMDEgMzAuOTk5NSAzMy4wMDAxSDNDMS44OTU0MyAzMy4wMDAxIDEgMzIuMTA0NiAxIDMxLjAwMDFWNS45MDk2N0MxIDQuODA1MSAxLjg5NTQzIDMuOTA5NjcgMyAzLjkwOTY3SDIzLjQyIiBmaWxsPSJ3aGl0ZSIvPgogICAgPHBhdGggZD0iTTI5LjA1NDEgMy45MDk2N0gzMC45OTk1QzMyLjEwNCAzLjkwOTY3IDMyLjk5OTUgNC44MDUxIDMyLjk5OTUgNS45MDk2N1YzMS4wMDAxQzMyLjk5OTUgMzIuMTA0NiAzMi4xMDQgMzMuMDAwMSAzMC45OTk1IDMzLjAwMDFIM0MxLjg5NTQzIDMzLjAwMDEgMSAzMi4xMDQ2IDEgMzEuMDAwMVY1LjkwOTY3QzEgNC44MDUxIDEuODk1NDMgMy45MDk2NyAzIDMuOTA5NjdIMjMuNDIiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNOC4yNzI0NiAzLjkwOTY3VjkuNzI3NzUiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMjguNjM1NyA5LjcyNzU0SDMyLjk5OTMiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMSA5LjcyNzU0SDE3LjYwMTkiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNMTcuOTA3NiAxNS4yNDFMMTQuOTk4NSAxMi4zMzJMMjUuNzI3MSAxLjYwMjcyQzI2LjUzMDcgMC43OTkwOTQgMjcuODMyNSAwLjc5OTA5NCAyOC42MzYxIDEuNjAyNzJDMjkuNDM5NyAyLjQwNjM0IDI5LjQzOTcgMy43MDgxNCAyOC42MzYxIDQuNTExNzZMMTcuOTA3NiAxNS4yNDFaIiBzdHJva2U9IiMyQzMzMzgiIHN0cm9rZS13aWR0aD0iMS41IiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogICAgPHBhdGggZD0iTTkuNDcyNDggMTYuNjUyQzEwLjYwODUgMTUuNTE2MSAxMi40NTA2IDE1LjUxNjEgMTMuNTg2NiAxNi42NTJDMTQuNzIyNiAxNy43ODggMTQuNzIyNiAxOS42MzAyIDEzLjU4NjYgMjAuNzY2MUMxMi40NTA2IDIxLjkwMjEgOC40NDQxNCAyMS43OTQ1IDguNDQ0MTQgMjEuNzk0NUM4LjQ0NDE0IDIxLjc5NDUgOC4zMzY1IDE3Ljc4OCA5LjQ3MjQ4IDE2LjY1MloiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8cGF0aCBkPSJNNi44MTc4NyAyNy4xODE5SDI3LjE4MTIiIHN0cm9rZT0iIzJDMzMzOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo=',
			'preview'     => 'https://try.gravitykit.com/demo/view/diy/?utm_source=plugin&utm_medium=try_demo&utm_campaign=view_type&utm_term=diy',
			'license'     => esc_html__( 'All Access', 'gk-gravityview' ),
			'textdomain'  => 'gravityview-diy|gk-diy',
		),
	);

	if ( ! class_exists( 'GravityKitFoundation' ) ) {
		return;
	}

	$product_manager = GravityKitFoundation::licenses()->product_manager();

	if ( ! $product_manager ) {
		return;
	}

	try {
		$products_data = $product_manager->get_products_data( array( 'key_by' => 'id' ) );
	} catch ( Exception $e ) {
		$products_data = array();
	}

	foreach ( $placeholders as $placeholder ) {
		if ( GravityKit\GravityView\Foundation\Helpers\Arr::get( $products_data, "{$placeholder['download_id']}.active" ) ) {
			// Template will be loaded by the extension.
			continue;
		}

		$placeholder['type']     = 'custom';
		$placeholder['included'] = ! empty( GravityKitFoundation::helpers()->array->get( $products_data, "{$placeholder['download_id']}.licenses" ) );

		new GravityView_Placeholder_Template( $placeholder['slug'], $placeholder );
	}
}
