<?php

defined('DOING_GRAVITYVIEW_TESTS') || exit;

/**
 * @group widgets
 *
 * @since 1.21
 */
class GravityView_Widget_Search_Test extends GV_UnitTestCase
{
    /**
     * @var \GravityView_Widget_Search
     */
    public $widget;

    public function setUp()
    {
        parent::setUp();
        $this->widget = new GravityView_Widget_Search();
    }

    /**
     * @covers GravityView_Widget_Search::filter_entries()
     * @group GravityView_Widget_Search
     *
     * @since 1.21
     */
    public function test_filter_entries()
    {
        $this->_test_word_splitting();

        // TODO: Cover prepare_field_filter() - Allow for all supported comparison types
    }

    private function _test_word_splitting()
    {
        $_GET = [];

        $this->assertEquals(['original value'], $this->widget->filter_entries(['original value'], null, [], true), 'when $_GET is empty, $search_criteria should be returned');

        $view = $this->factory->view->create_and_get([
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'search_all'],
                        ['field' => 'entry_id'],
                        ['field' => 'entry_date'],
                        ['field' => 'created_by'],
                    ]),
                ],
            ]],
        ]);
        $args = ['id' => $view->ID];

        $search_criteria_single = [
            'field_filters' => [
                'mode' => 'any',
                [
                    'key'      => null,
                    'value'    => 'with spaces',
                    'operator' => 'contains',
                ],
            ],
        ];

        $_GET = [
            'gv_search' => ' with  spaces',
        ];
        add_filter('gravityview/search-all-split-words', '__return_false');
        $this->assertEquals($search_criteria_single, $this->widget->filter_entries([], null, $args, true));
        remove_filter('gravityview/search-all-split-words', '__return_false');

        $search_criteria_split = [
            'field_filters' => [
                'mode' => 'any',
                [
                    'key'      => null,
                    'value'    => 'with',
                    'operator' => 'contains',
                ],
                [
                    'key'      => null,
                    'value'    => 'spaces',
                    'operator' => 'contains',
                ],
            ],
        ];

        $_GET = [
            'gv_search' => ' with  spaces',
        ];
        $this->assertEquals($search_criteria_split, $this->widget->filter_entries([], null, $args, true));

        $_GET = [
            'gv_search' => '%20with%20%20spaces',
        ];
        $this->assertEquals($search_criteria_split, $this->widget->filter_entries([], null, $args, true));

        $_GET = [
            'gv_search' => '%20with%20%20spaces',
        ];

        $search_criteria_split_mode = $search_criteria_split;
        $search_criteria_split_mode['field_filters']['mode'] = 'all';

        add_filter('gravityview/search/mode', function () { return 'all'; });
        $this->assertEquals($search_criteria_split_mode, $this->widget->filter_entries([], null, $args, true));
        remove_all_filters('gravityview/search/mode');

        $_GET = [
            'gv_search' => 'with%20%20spaces',
            'mode'      => 'all',
        ];
        $this->assertEquals($search_criteria_split_mode, $this->widget->filter_entries([], null, $args, true));

        // Test ?gv_id param
        $_GET = [
            'gv_search' => 'with%20spaces',
            'gv_id'     => 12,
            'gv_by'     => 547,
        ];
        $search_criteria_with_more_params = $search_criteria_split;
        $search_criteria_with_more_params['field_filters'][] = [
            'key'      => 'id',
            'value'    => 12,
            'operator' => '=',
        ];
        $search_criteria_with_more_params['field_filters'][] = [
            'key'      => 'created_by',
            'value'    => 547,
            'operator' => '=',
        ];

        $this->assertEquals($search_criteria_with_more_params, $this->widget->filter_entries([], null, $args, true));

        $start = '1997-03-28';
        $end = '2017-10-03';

        add_filter('gravityview/widgets/search/datepicker/format', function () { return 'ymd_dash'; });

        // Test dates
        $_GET = [
            'gv_start' => $start,
            'gv_end'   => $end,
        ];

        $search_criteria_dates = [
            'start_date'    => get_gmt_from_date($start),
            'end_date'      => get_gmt_from_date('2017-10-03 23:59:59' /* + 1 day */),
            'field_filters' => [
                'mode' => 'any',
            ],
        ];
        $this->assertEquals($search_criteria_dates, $this->widget->filter_entries([], null, $args, true));

        $_GET = [];

        remove_all_filters('gravityview/widgets/search/datepicker/format');
    }

    public function test_search_limited_fields()
    {
        /**
         * gv_search query parameter.
         *
         * SHOULD NOT search if "search_all" setting is not set in Search Widget "Search Field" setting.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => '4'],
                    ]),
                ],
            ]],
        ]);

        add_filter('gravityview/widgets/search/datepicker/format', function () { return 'ymd_dash'; });

        $_GET = ['gv_search' => '_'];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        /**
         * gv_search query paramter.
         *
         * SHOULD NOT search in non-visible fields.
         *
         * @todo impossible as it would require an "any" relationship
         *  so return to this when new query implementation is ready.
         */

        /**
         * gv_start, gv_end query parameters.
         *
         * SHOULD NOT search unless "entry_date" is set in Search Widget "Search Field" settings.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => '1.1'],
                    ]),
                ],
            ]],
        ]);

        $_GET = ['gv_start' => '2017-01-01', 'gv_end' => '2017-12-31'];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        /**
         * gv_start, gv_end query parameters.
         *
         * SHOULD NOT search outside of the View settings Start Date or End Dates, if set.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'settings' => [
                'start_date' => '2017-05-01',
            ],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'entry_date'],
                    ]),
                ],
            ]],
        ]);

        $_GET = ['gv_start' => '2017-01-01', 'gv_end' => '2017-12-31'];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
            ],
            'start_date' => '2017-05-01 00:00:00',
            'end_date'   => '2017-12-31 23:59:59',
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        /**
         * gv_id query parameter.
         *
         * SHOULD NOT search unless "entry_id" is set in Search Widget "Search Field" settings.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'entry_date'],
                    ]),
                ],
            ]],
        ]);

        $_GET = ['gv_id' => '_'];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        /**
         * gv_by query parameter.
         *
         * SHOULD NOT search unless "created_by" is set in Search Widget "Search Field" settings.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'entry_id'],
                    ]),
                ],
            ]],
        ]);

        $_GET = ['gv_by' => '1', 'gv_id' => '3'];

        $search_criteria = [
            'field_filters' => [
                [
                    'key'      => 'id',
                    'value'    => '3',
                    'operator' => '=',
                ],
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        /**
         * gv_by query parameter.
         *
         * SHOULD NOT search unless "created_by" is set in Search Widget "Search Field" settings.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'entry_id'],
                        ['field' => 'created_by'],
                    ]),
                ],
            ]],
        ]);

        $_GET = ['gv_by' => '1', 'gv_id' => '3'];

        $search_criteria = [
            'field_filters' => [
                [
                    'key'      => 'id',
                    'value'    => '3',
                    'operator' => '=',
                ],
                [
                    'key'      => 'created_by',
                    'value'    => '1',
                    'operator' => '=',
                ],
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        /**
         * filter_* query parameters.
         *
         * SHOULD NOT search if field is absent from Search Widget "Search Field" settings.
         */
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => '1.1'],
                    ]),
                ],
            ]],
        ]);

        $_GET = ['filter_1_2' => '_'];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        $_GET = ['input_1_2' => '_'];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        $_GET = [];

        remove_all_filters('gravityview/widgets/search/datepicker/format');
    }

    public function test_filter_entries_gv_start_end_time()
    {
        $_GET = [
            'gv_start' => '2018-04-07',
            'gv_end'   => '2018-04-07',
        ];

        add_filter('gravityview/widgets/search/datepicker/format', function () { return 'ymd_dash'; });

        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'entry_date'],
                    ]),
                ],
            ]],
        ]);

        add_filter('pre_option_timezone_string', $callback = function () {
            return 'Etc/GMT+0';
        });

        $search_criteria_dates = [
            'start_date'    => '2018-04-07 00:00:00',
            'end_date'      => '2018-04-07 23:59:59',
            'field_filters' => [
                'mode' => 'any',
            ],
        ];
        $this->assertEquals($search_criteria_dates, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        remove_filter('pre_option_timezone_string', $callback);

        add_filter('gravityview_date_created_adjust_timezone', '__return_true');
        add_filter('pre_option_timezone_string', $callback = function () {
            return 'Etc/GMT+5';
        });

        $search_criteria_dates = [
            'start_date'    => '2018-04-07 05:00:00',
            'end_date'      => '2018-04-08 05:00:00',
            'field_filters' => [
                'mode' => 'any',
            ],
        ];
        $this->assertEquals($search_criteria_dates, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        add_filter('gravityview_date_created_adjust_timezone', '__return_true');
        remove_filter('pre_option_timezone_string', $callback);

        $_GET = [];

        remove_all_filters('gravityview/widgets/search/datepicker/format');
    }

    /**
     * @dataProvider get_gv_start_end_formats
     */
    public function test_filter_entries_gv_start_end_formats($format, $dates, $name)
    {
        $view = $this->factory->view->create_and_get([
            'fields' => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => 'entry_date'],
                    ]),
                ],
            ]],
        ]);

        $search_criteria_dates = [
            'start_date'    => '2018-02-01 00:00:00',
            'end_date'      => '2018-04-03 23:59:59',
            'field_filters' => [
                'mode' => 'any',
            ],
        ];

        $_GET = $dates;

        add_filter('gravityview/widgets/search/datepicker/format', function () use ($name) { return $name; });

        $this->assertEquals($search_criteria_dates, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        remove_all_filters('gravityview/widgets/search/datepicker/format');

        $_GET = [];
    }

    /**
     * https://docs.gravityview.co/article/115-changing-the-format-of-the-search-widgets-date-picker.
     */
    public function get_gv_start_end_formats()
    {
        return [
            ['mm/dd/yyyy', ['gv_start' => '02/01/2018', 'gv_end' => '04/03/2018'], 'mdy'],
            ['mm/dd/yyyy', ['gv_start' => '02/01/2018', 'gv_end' => '04/03/2018'], 'invalid! This should result in mdy.'],

            ['yyyy-mm-dd', ['gv_start' => '2018-02-01', 'gv_end' => '2018-04-03'], 'ymd_dash'],
            ['yyyy/mm/dd', ['gv_start' => '2018/02/01', 'gv_end' => '2018/04/03'], 'ymd_slash'],
            ['yyyy.mm.dd', ['gv_start' => '2018.02.01', 'gv_end' => '2018.04.03'], 'ymd_dot'],

            ['dd/mm/yyyy', ['gv_start' => '01/02/2018', 'gv_end' => '03/04/2018'], 'dmy'],
            ['dd-mm-yyyy', ['gv_start' => '01-02-2018', 'gv_end' => '03-04-2018'], 'dmy_dash'],
            ['dd.mm.yyyy', ['gv_start' => '01.02.2018', 'gv_end' => '03.04.2018'], 'dmy_dot'],
        ];
    }

    /**
     * @dataProvider get_date_filter_formats
     */
    public function test_date_filter_formats($format, $dates, $name)
    {
        $form = $this->factory->form->import_and_get('complete.json');

        global $post;

        $view = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(4, false) => [
                        'id'    => '3',
                        'label' => 'Date',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"search_all","input":"input_text"}]',
                    ],
                ],
            ],
        ]);

        $search_criteria_dates = [
            'field_filters' => [
                'mode' => 'any',
                [
                    'key'      => '3',
                    'value'    => '2018-02-01',
                    'form_id'  => $form['id'],
                    'operator' => 'is',
                ],
            ],
        ];

        $_GET = $dates;

        add_filter('gravityview/widgets/search/datepicker/format', function () use ($name) { return $name; });

        $this->assertEquals($search_criteria_dates, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        remove_all_filters('gravityview/widgets/search/datepicker/format');

        $_GET = [];
    }

    public function get_date_filter_formats()
    {
        return [
            ['mm/dd/yyyy', ['filter_3' => '02/01/2018'], 'mdy'],
            ['mm/dd/yyyy', ['filter_3' => '02/01/2018'], 'invalid! This should result in mdy.'],

            ['yyyy-mm-dd', ['filter_3' => '2018-02-01'], 'ymd_dash'],
            ['yyyy/mm/dd', ['filter_3' => '2018/02/01'], 'ymd_slash'],
            ['yyyy.mm.dd', ['filter_3' => '2018.02.01'], 'ymd_dot'],

            ['dd/mm/yyyy', ['filter_3' => '01/02/2018'], 'dmy'],
            ['dd-mm-yyyy', ['filter_3' => '01-02-2018'], 'dmy_dash'],
            ['dd.mm.yyyy', ['filter_3' => '01.02.2018'], 'dmy_dot'],

            ['mm/dd/yyyy', ['input_3' => '02/01/2018'], 'mdy'],
            ['mm/dd/yyyy', ['input_3' => '02/01/2018'], 'invalid! This should result in mdy.'],

            ['yyyy-mm-dd', ['input_3' => '2018-02-01'], 'ymd_dash'],
            ['yyyy/mm/dd', ['input_3' => '2018/02/01'], 'ymd_slash'],
            ['yyyy.mm.dd', ['input_3' => '2018.02.01'], 'ymd_dot'],

            ['dd/mm/yyyy', ['input_3' => '01/02/2018'], 'dmy'],
            ['dd-mm-yyyy', ['input_3' => '01-02-2018'], 'dmy_dash'],
            ['dd.mm.yyyy', ['input_3' => '01.02.2018'], 'dmy_dot'],
        ];
    }

    public function test_search_is_approved_gf_query()
    {
        if (!gravityview()->plugin->supports(\GV\Plugin::FEATURE_GFQUERY)) {
            $this->markTestSkipped('Requires \GF_Query from Gravity Forms 2.3');
        }

        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'settings'    => [
                'show_only_approved' => true,
            ],
            'fields' => [
                'directory_table-columns' => [wp_generate_password(4, false) => [
                    'id'    => '4',
                    'label' => 'Email',
                ],
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"4","input":"input_text"},{"field":"16","input":"input_text"}]',
                        'search_mode'   => 'any',
                    ],
                ],
            ],
        ]);
        $view = \GV\View::from_post($post);

        /** Approved entry. */
        $entry = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',

            '4'  => 'support@gravityview.co',
            '16' => 'Contact us if you have any questions.',
        ]);
        gform_update_meta($entry['id'], \GravityView_Entry_Approval::meta_key, \GravityView_Entry_Approval_Status::APPROVED);

        /** Approved sentinel. */
        $entry = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',

            '4'  => 'gravityview.co',
            '16' => 'Our website.',
        ]);
        gform_update_meta($entry['id'], \GravityView_Entry_Approval::meta_key, \GravityView_Entry_Approval_Status::APPROVED);

        /** Unapproved entry. */
        $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',

            '4'  => 'support@gravityview.co',
            '16' => 'Contact us if you have any questions.',
        ]);

        $_GET = [
            'filter_4'  => 'support',
            'filter_16' => 'support', // In mode "any" this should be ignored
        ];

        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = [
            'input_4'  => 'support',
            'input_16' => 'support', // In mode "any" this should be ignored
        ];

        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = [];
    }

    /**
     * @dataProvider get_test_approval_status_search
     */
    public function test_approval_status_search($show_only_approved, $statuses, $counts)
    {
        if (!gravityview()->plugin->supports(\GV\Plugin::FEATURE_GFQUERY)) {
            $this->markTestSkipped('Requires \GF_Query from Gravity Forms 2.3');
        }

        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [wp_generate_password(4, false) => [
                    'id'    => '4',
                    'label' => 'Email',
                ],
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'settings' => [
                'show_only_approved' => $show_only_approved,
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"is_approved","input":"checkbox"}]',
                    ],
                ],
            ],
        ]);

        $view = \GV\View::from_post($post);

        $did_unapproved_meta = false;

        foreach (['approved', 'disapproved', 'unapproved'] as $status) {
            foreach (range(1, $statuses[$status]) as $_) {
                $entry = $this->factory->entry->create_and_get([
                    'form_id' => $form['id'],
                    'status'  => 'active',
                    '16'      => wp_generate_password(16, false),
                ]);

                if ('unapproved' === $status) {
                    if (!$did_unapproved_meta) { // Test both unapproved meta, and empty approval value meta
                        gform_update_meta($entry['id'], \GravityView_Entry_Approval::meta_key, \GravityView_Entry_Approval_Status::UNAPPROVED);
                        $did_unapproved_meta = true;
                    }
                    continue;
                }

                gform_update_meta($entry['id'], \GravityView_Entry_Approval::meta_key, $status === 'approved' ? \GravityView_Entry_Approval_Status::APPROVED : \GravityView_Entry_Approval_Status::DISAPPROVED);
            }
        }

        /** Show all. */
        foreach ($counts as $count) {
            $_GET = [
                'filter_is_approved' => $count['filter'],
            ];
            $this->assertEquals($count['count'], $view->get_entries()->count());
        }

        $_GET = [];
    }

    public function get_test_approval_status_search()
    {
        return [
            [
                'show_only_approved' => false,
                'statuses'           => [
                    'unapproved'  => 2,
                    'approved'    => 5,
                    'disapproved' => 8,
                ],
                'counts'      => [
                    ['count' => 15, 'filter' => []],
                    ['count' => 2, 'filter' => [\GravityView_Entry_Approval_Status::UNAPPROVED]],
                    ['count' => 5, 'filter' => [\GravityView_Entry_Approval_Status::APPROVED]],
                    ['count' => 8, 'filter' => [\GravityView_Entry_Approval_Status::DISAPPROVED]],
                    ['count' => 0, 'filter' => [-1]],
                ],
            ],
            [
                'show_only_approved' => true,
                'statuses'           => [
                    'unapproved'  => 2,
                    'approved'    => 5,
                    'disapproved' => 8,
                ],
                'counts'      => [
                    ['count' => 5, 'filter' => []],
                    ['count' => 0, 'filter' => [\GravityView_Entry_Approval_Status::UNAPPROVED]],
                    ['count' => 5, 'filter' => [\GravityView_Entry_Approval_Status::APPROVED]],
                    ['count' => 0, 'filter' => [\GravityView_Entry_Approval_Status::DISAPPROVED]],
                    ['count' => 0, 'filter' => [-1]],
                ],
            ],
        ];
    }

    public function test_created_by_multi_search()
    {
        if (!gravityview()->plugin->supports(\GV\Plugin::FEATURE_GFQUERY)) {
            $this->markTestSkipped('Requires \GF_Query from Gravity Forms 2.3');
        }

        $alpha = $this->factory->user->create([
            'user_login' => 'alpha',
            'user_email' => md5(microtime()).'@gravityview.tests',
        ]);

        $this->assertTrue(is_int($alpha) && !empty($alpha));

        $beta = $this->factory->user->create([
            'user_login' => 'beta',
            'user_email' => md5(microtime()).'@gravityview.tests',
        ]);

        $this->assertTrue(is_int($beta) && !empty($beta));

        $gamma = $this->factory->user->create([
            'user_login' => 'gamma',
            'user_email' => md5(microtime()).'@gravityview.tests',
        ]);

        $this->assertTrue(is_int($gamma) && !empty($gamma));

        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"created_by","input":"checkbox"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $this->factory->entry->create_and_get([
            'form_id'    => $form['id'],
            'created_by' => $alpha,
            'status'     => 'active',
            '16'         => wp_generate_password(16, false),
        ]);

        $this->factory->entry->create_and_get([
            'form_id'    => $form['id'],
            'created_by' => $beta,
            'status'     => 'active',
            '16'         => wp_generate_password(16, false),
        ]);

        $this->factory->entry->create_and_get([
            'form_id'    => $form['id'],
            'created_by' => $gamma,
            'status'     => 'active',
            '16'         => wp_generate_password(16, false),
        ]);

        $_GET = [];
        $this->assertEquals(3, $view->get_entries()->count());

        $_GET = ['gv_by' => $alpha];
        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = ['gv_by' => [$alpha, $beta]];
        $this->assertEquals(2, $view->get_entries()->count());

        $_GET = ['gv_by' => -1];
        $this->assertEquals(0, $view->get_entries()->count());

        $_GET = [];
    }

    public function test_created_by_text_search()
    {
        if (!gravityview()->plugin->supports(\GV\Plugin::FEATURE_GFQUERY)) {
            $this->markTestSkipped('Requires \GF_Query from Gravity Forms 2.3');
        }

        $alpha = $this->factory->user->create([
            'user_login' => 'alpha',
            'user_email' => md5(microtime()).'@gravityview.tests',
        ]);

        $beta = $this->factory->user->create([
            'user_login' => 'beta',
            'user_email' => md5(microtime()).'@gravityview.tests',
        ]);

        $gamma = $this->factory->user->create([
            'user_login' => 'gamma',
            'user_email' => md5(microtime()).'@gravityview.tests',
        ]);

        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"created_by","input":"input_text"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $this->factory->entry->create_and_get([
            'form_id'    => $form['id'],
            'created_by' => $alpha,
            'status'     => 'active',
            '16'         => wp_generate_password(16, false),
        ]);

        $this->factory->entry->create_and_get([
            'form_id'    => $form['id'],
            'created_by' => $beta,
            'status'     => 'active',
            '16'         => wp_generate_password(16, false),
        ]);

        $this->factory->entry->create_and_get([
            'form_id'    => $form['id'],
            'created_by' => $gamma,
            'status'     => 'active',
            '16'         => wp_generate_password(16, false),
        ]);

        $_GET = [];
        $this->assertEquals(3, $view->get_entries()->count());

        $_GET = ['gv_by' => 'a'];
        $this->assertEquals(3, $view->get_entries()->count());

        $_GET = ['gv_by' => 'mm'];
        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = ['gv_by' => 'beta'];
        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = ['gv_by' => 'gravityview.tests'];
        $this->assertEquals(3, $view->get_entries()->count());

        $_GET = ['gv_by' => 'custom'];
        $this->assertEquals(0, $view->get_entries()->count());

        update_user_meta($gamma, 'custom_meta', 'custom');
        add_filter('gravityview/widgets/search/created_by/user_meta_fields', function () {
            return ['custom_meta'];
        });
        $this->assertEquals(1, $view->get_entries()->count());

        remove_all_filters('gravityview/widgets/search/created_by/user_meta_fields');

        $_GET = [];
    }

    /**
     * https://gist.github.com/zackkatz/66e9fb2147a9eb1a2f2e.
     */
    public function test_override_search_operator()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"16","input":"input_text"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello world',
        ]);

        $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello',
        ]);

        $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'world',
        ]);

        $_GET = [];
        $this->assertEquals(3, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello'];
        $this->assertEquals(2, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello'];
        $this->assertEquals(2, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello'];
        $this->assertEquals(2, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'world'];
        $this->assertEquals(2, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'world'];
        $this->assertEquals(2, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello world, goodbye moon'];
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world, goodbye moon'];
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        add_filter('gravityview_fe_search_criteria', $callback = function ($search_criteria) {
            if (!isset($search_criteria['field_filters'])) {
                return $search_criteria;
            }

            foreach ($search_criteria['field_filters'] as $k => $filter) {
                if (!empty($filter['key']) && '16' == $filter['key']) {
                    $search_criteria['field_filters'][$k]['operator'] = 'is';
                    break;
                }
            }

            return $search_criteria;
        });

        $_GET = [];
        $this->assertEquals(3, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello world, goodbye moon'];
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world, goodbye moon'];
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        remove_filter('gravityview_fe_search_criteria', $callback);

        add_filter('gravityview_search_operator', $callback = function ($operator, $field) {
            if ($field['key'] == '16') {
                return 'is';
            }

            return $operator;
        }, 10, 2);

        $_GET = [];
        $this->assertEquals(3, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello world, goodbye moon'];
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = ['filter_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world, goodbye moon'];
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = ['input_16' => 'hello world'];
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        remove_filter('gravityview_search_operator', $callback);

        $_GET = [];
    }

    /**
     * https://github.com/gravityview/GravityView/issues/1233.
     */
    public function test_search_date_created()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"entry_date","input":"date_range"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $this->factory->entry->create_and_get([
            'form_id'      => $form['id'],
            'status'       => 'active',
            'date_created' => '2019-01-03 12:00:00',
            '16'           => 'hello world',
        ]);

        $this->factory->entry->create_and_get([
            'form_id'      => $form['id'],
            'status'       => 'active',
            'date_created' => '2019-01-04 12:00:00',
            '16'           => 'hello',
        ]);

        $this->factory->entry->create_and_get([
            'form_id'      => $form['id'],
            'status'       => 'active',
            'date_created' => '2019-01-05 12:00:00',
            '16'           => 'world',
        ]);

        $_GET = [];
        $this->assertEquals(3, $view->get_entries()->fetch()->count());

        $_GET['gv_start'] = '01/01/2019';
        $_GET['gv_end'] = '01/01/2019';
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET['gv_start'] = '01/04/2019';
        $_GET['gv_end'] = '01/04/2019';
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET['gv_start'] = '01/06/2019';
        $_GET['gv_end'] = '01/06/2019';
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET = [];
    }

    public function test_payment_date_search()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => 'payment_date',
                        'label' => 'Payment Date',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"payment_date","input":"date"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $this->factory->entry->create_and_get([
            'form_id'      => $form['id'],
            'status'       => 'active',
            'payment_date' => '2020-11-20 12:00:00',
        ]);

        $_GET = [];

        $_GET['filter_payment_date'] = '12/20/2020';
        $this->assertEquals(0, $view->get_entries()->fetch()->count());

        $_GET['filter_payment_date'] = '11/20/2020';
        $this->assertEquals(1, $view->get_entries()->fetch()->count());

        $_GET = [];
    }

    public function test_operator_url_overrides()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"16","input":"input_text"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $hello_world = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello world',
        ]);

        $hello = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello',
        ]);

        $world = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'world',
        ]);

        $_GET = [];
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(3, $entries);

        $_GET['filter_16'] = 'hello';
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(2, $entries);

        $_GET['input_16'] = 'hello';
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(2, $entries);

        $_GET['filter_16'] = 'hello';
        $_GET['filter_16|op'] = '!='; // Override doesn't work, as '!=' is not in allowlist
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(2, $entries);
        $this->assertEquals($hello['id'], $entries[0]['id']);
        $this->assertEquals($hello_world['id'], $entries[1]['id']);

        $_GET['input_16'] = 'hello';
        $_GET['input_16|op'] = '!='; // Override doesn't work, as '!=' is not in allowlist
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(2, $entries);
        $this->assertEquals($hello['id'], $entries[0]['id']);
        $this->assertEquals($hello_world['id'], $entries[1]['id']);

        add_filter('gravityview/search/operator_allowlist', $callback = function () {
            return ['!='];
        });

        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(2, $entries);
        $this->assertEquals($world['id'], $entries[0]['id']);
        $this->assertEquals($hello_world['id'], $entries[1]['id']);

        remove_filter('gravityview/search/operator_allowlist', $callback);

        $_GET = [];
    }

    public function test_search_all_basic()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"search_all","input":"input_text"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $hello_world = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello world',
        ]);

        $hello = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello',
        ]);

        $world = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'world',
        ]);

        $_GET = [];
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(3, $entries);

        $_GET['gv_search'] = 'hello';
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(2, $entries);

        $_GET = [];
    }

    public function test_search_all_basic_choices()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(16, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                    wp_generate_password(16, false) => [
                        'id'    => '2',
                        'label' => 'Checkbox',
                    ],
                ],
            ],
            'widgets' => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"search_all","input":"input_text"}]',
                    ],
                ],
            ],
            'settings' => $settings,
        ]);
        $view = \GV\View::from_post($post);

        $hello_world = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'wazzup',
            '2.2'     => 'Somewhat Better',
        ]);

        $hello = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'hello',
        ]);

        $_GET['gv_search'] = 'better';
        $entries = $view->get_entries()->fetch()->all();
        $this->assertCount(1, $entries);

        $_GET = [];
    }

    public function test_searchable_field_restrictions_filter()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $settings = \GV\View_Settings::defaults();
        $settings['show_only_approved'] = 0;
        $post = $this->factory->view->create_and_get([
            'form_id' => $form['id'],
            'fields'  => ['_' => [
                ['id' => '1.1'],
            ]],
            'widgets' => ['_' => [
                [
                    'id'            => 'search_bar',
                    'search_fields' => json_encode([
                        ['field' => '1.1', 'input' => 'text'],
                    ]),
                ],
            ]],
            'settings' => $settings,
        ]);

        $view = \GV\View::from_post($post);

        $_GET = [
            'gv_start'   => '2017-01-01',
            'gv_end'     => '2017-12-31',
            'filter_1_1' => 'hello',
            'filter_16'  => 'world',
        ];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
                [
                    'key'      => '1.1',
                    'value'    => 'hello',
                    'form_id'  => $view->form->ID,
                    'operator' => 'contains',
                ],
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        $_GET = [
            'gv_start'  => '2017-01-01',
            'gv_end'    => '2017-12-31',
            'input_1_1' => 'hello',
            'input_16'  => 'world',
        ];

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
                [
                    'key'      => '1.1',
                    'value'    => 'hello',
                    'form_id'  => $view->form->ID,
                    'operator' => 'contains',
                ],
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        add_filter($filter = 'gravityview/search/searchable_fields/allowlist', $callback = function ($fields, $view, $with_full) {
            if ($with_full) {
                return [
                    [
                        'field'   => '16',
                        'form_id' => $view->form->ID,
                        'input'   => 'text',
                    ],
                ];
            } else {
                return ['16'];
            }
        }, 10, 3);

        $search_criteria = [
            'field_filters' => [
                'mode' => 'any',
                [
                    'key'      => '16',
                    'value'    => 'world',
                    'form_id'  => $view->form->ID,
                    'operator' => 'contains',
                ],
            ],
        ];

        $this->assertEquals($search_criteria, $this->widget->filter_entries([], null, ['id' => $view->ID], true));

        remove_filter($filter, $callback);
    }

    public function test_search_value_trimming()
    {
        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'settings'    => [
                'show_only_approved' => false,
            ],
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(4, false) => [
                        'id'    => '16',
                        'label' => 'Textarea',
                    ],
                ],
            ],
            'widgets'     => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => '[{"field":"search_all","input":"input_text"},{"field":"16","input":"input_text"}]',
                        'search_mode'   => 'any',
                    ],
                ],
            ],
        ]);

        $view = \GV\View::from_post($post);

        $entry = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'Text ',
        ]);

        $entry = $this->factory->entry->create_and_get([
            'form_id' => $form['id'],
            'status'  => 'active',
            '16'      => 'Text',
        ]);

        // Whitespaces are trimmed by default
        $_GET = ['filter_16' => 'Text '];
        $this->assertEquals(2, $view->get_entries()->count());
        $_GET = ['input_16' => 'Text '];
        $this->assertEquals(2, $view->get_entries()->count());
        $_GET = ['gv_search' => 'Text '];
        $this->assertEquals(2, $view->get_entries()->count());

        // Retain whitespaces via a filter
        add_filter('gravityview/search-trim-input', '__return_false');
        add_filter('gravityview/search-all-split-words', '__return_false'); // This is to ensure that "Text " is not split to ["Text", ""]
        $_GET = ['filter_16' => 'Text '];
        $this->assertEquals(1, $view->get_entries()->count());
        $_GET = ['input_16' => 'Text '];
        $this->assertEquals(1, $view->get_entries()->count());
        $_GET = ['gv_search' => 'Text '];
        $this->assertEquals(1, $view->get_entries()->count());
        remove_filter('gravityview/search-trim-input', '__return_false');
        remove_filter('gravityview/search-all-split-words', '__return_false');

        $_GET = [];
    }

    public function test_search_with_strict_empty_value_matching()
    {
        if (!gravityview()->plugin->supports(\GV\Plugin::FEATURE_GFQUERY)) {
            $this->markTestSkipped('Requires \GF_Query from Gravity Forms 2.3');
        }

        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(4, false)  => [
                        'id'    => '8.3',
                        'label' => 'First',
                    ],
                    wp_generate_password(16, false) => [
                        'id'    => '8.6',
                        'label' => 'Last',
                    ],
                ],
            ],
            'settings'    => [
                'show_only_approved' => false,
            ],
            'widgets'     => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => json_encode(
                            [
                                [
                                    'field' => '8.3',
                                ],
                                [
                                    'field' => '8.6',
                                ],
                            ]
                        ),
                    ],
                ],
            ],
        ]);

        $view = \GV\View::from_post($post);

        $data = [
            ['Alice', 'Alice'],
            ['Alice', 'Bob'],
            ['Alice', 'Alice'],
        ];

        foreach ($data as $name) {
            $this->factory->entry->create_and_get([
                'form_id' => $form['id'],
                'status'  => 'active',
                '8.3'     => $name[0],
                '8.6'     => $name[1],
            ]);
        }

        // Default "contains" operator
        $_GET = ['filter_8_3' => 'Alice', 'filter_8_6' => '', 'mode' => 'all'];

        $this->assertEquals(3, $view->get_entries()->count());

        // Default "contains" operator
        $_GET = ['input_8_3' => 'Alice', 'input_8_6' => '', 'mode' => 'all'];

        $this->assertEquals(3, $view->get_entries()->count());

        // "is" operator
        add_filter('gravityview_search_operator', function () {
            return 'is';
        });

        // do not ignore empty values
        add_filter('gravityview/search/ignore-empty-values', '__return_false');

        $this->assertEquals(0, $view->get_entries()->count());

        $_GET = ['filter_8_3' => 'Alice', 'filter_8_6' => 'Alice', 'mode' => 'all'];

        $this->assertEquals(2, $view->get_entries()->count());

        $_GET = ['input_8_3' => 'Alice', 'input_8_6' => 'Alice', 'mode' => 'all'];

        $this->assertEquals(2, $view->get_entries()->count());

        remove_all_filters('gravityview_search_operator');

        $_GET = [];
    }

    public function test_search_with_number_field()
    {
        if (!gravityview()->plugin->supports(\GV\Plugin::FEATURE_GFQUERY)) {
            $this->markTestSkipped('Requires \GF_Query from Gravity Forms 2.3');
        }

        $form = $this->factory->form->import_and_get('complete.json');
        $post = $this->factory->view->create_and_get([
            'form_id'     => $form['id'],
            'template_id' => 'table',
            'fields'      => [
                'directory_table-columns' => [
                    wp_generate_password(4, false)  => [
                        'id'    => '9',
                        'label' => 'Number',
                    ],
                ],
            ],
            'settings'    => [
                'show_only_approved' => false,
            ],
            'widgets'     => [
                'header_top' => [
                    wp_generate_password(4, false) => [
                        'id'            => 'search_bar',
                        'search_fields' => json_encode(
                            [
                                [
                                    'field' => '9',
                                ],
                            ]
                        ),
                    ],
                ],
            ],
        ]);

        $view = \GV\View::from_post($post);

        foreach ([1, 5, 7, 10] as $number) {
            $this->factory->entry->create_and_get([
                'form_id' => $form['id'],
                'status'  => 'active',
                '9'       => $number,
            ]);
        }

        // "is" operator
        add_filter('gravityview_search_operator', function () {
            return 'is';
        });

        // do not ignore empty values
        add_filter('gravityview/search/ignore-empty-values', '__return_false');

        $_GET = ['filter_9' => '5', 'mode' => 'all'];

        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = ['filter_9' => '', 'mode' => 'all'];

        $this->assertEquals(0, $view->get_entries()->count());

        $_GET = ['input_9' => '5', 'mode' => 'all'];

        $this->assertEquals(1, $view->get_entries()->count());

        $_GET = ['input_9' => '', 'mode' => 'all'];

        $this->assertEquals(0, $view->get_entries()->count());

        remove_all_filters('gravityview_search_operator');

        $_GET = [];
    }
}
