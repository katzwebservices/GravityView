<?php
/**
 * Plugin Name: GF Importer
 * Description: Imports Gravity Forms and Entries via WP-CLI or based on a query parameter.
 */

function gf_import_forms_or_entries() {
    if (defined('WP_CLI') && WP_CLI) {
        $args = WP_CLI::get_runner()->arguments;
        $command = isset($args[0]) ? $args[0] : null;

        if ($command === 'import_forms_and_entries') {
            gf_import_forms_and_entries();
        } else {
            WP_CLI::error('Invalid command. Use "import_forms_and_entries".');
        }
        exit;
    }

    if (isset($_GET['import_gf_forms_entries']) && $_GET['import_gf_forms_entries'] === 'true') {
        gf_import_forms_and_entries();
    }
}

function gf_import_forms_and_entries() {
    if (!class_exists('GFAPI')) {
        wp_die('Gravity Forms is not activated or the GFAPI class is not available.');
    }

    $data_dir = plugin_dir_path(__FILE__) . 'forms_and_entries/';

    if (!is_dir($data_dir)) {
        wp_die('Forms and entries directory does not exist.');
    }

    $data_files = glob($data_dir . '*.json');

    if (empty($data_files)) {
        wp_die('No JSON files found in the forms and entries directory.');
    }

    foreach ($data_files as $file) {
        $data = file_get_contents($file);
        $decoded_data = json_decode($data, true);

        if (isset($decoded_data['form'])) {
            $form = $decoded_data['form'];
            $form_id = GFAPI::add_form($form);

            if (is_wp_error($form_id)) {
                WP_CLI::error('Error importing form from ' . basename($file) . ': ' . $form_id->get_error_message());
            } else {
                WP_CLI::success('Successfully imported form ' . $form['title'] . ' with ID ' . $form_id);

                if (isset($decoded_data['entries']) && is_array($decoded_data['entries'])) {
                    foreach ($decoded_data['entries'] as $entry) {
                        $entry['form_id'] = $form_id;
                        $result = GFAPI::add_entry($entry);

                        if (is_wp_error($result)) {
                            WP_CLI::error('Error importing entry for form ID ' . $form_id . ' from ' . basename($file) . ': ' . $result->get_error_message());
                        } else {
                            WP_CLI::success('Successfully imported entry for form ID ' . $form_id);
                        }
                    }
                }
            }
        } else {
            WP_CLI::error('Invalid or missing form data in file: ' . basename($file));
        }
    }
}

add_action('init', 'gf_import_forms_or_entries');