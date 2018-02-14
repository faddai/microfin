<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/03/2017
 * Time: 02:36
 */

return [
    'dateFormat' => 'jS M, Y',

    'limit' => env('PAGINATION_LIMIT', 50),

    'reports' => [
        /*
         * Loan Reports
         */
        'loans' => [
            /*
             * PAR
             */
            [
                'title' => 'Portfolio At Risk',
                'description' => ''
            ],

            /*
             * Loan Book
             */
            [
                'title' => 'Loan Book',
                'description' => ''
            ],

            /*
             * Ageing Analysis
             */
            [
                'title' => 'Ageing Analysis',
                'description' => ''
            ],

            /*
             * CRB
             */
            [
                'title' => 'CRB Monthly Report',
                'description' => '',
            ],

            /*
             * Loan Collections Report
             */
            [
                'title' => 'Collections Report',
                'description' => 'A report on actual Loan repayment collections',
            ],

            /*
             * Maturity Ladder Report
             */
            [
                'title' => 'Maturity Ladder Report',
                'description' => '',
            ],

            /*
             * Recovery Projections Report
             */
            [
                'title' => 'Monthly Collection Projections',
                'description' => '',
            ],

            /*
             * Customer Data Report
             */
            [
                'title' => 'Customer Data Report',
                'description' => '',
            ],

            /*
             * Gender Ratio
             */
            [
                'title' => 'Gender Ratio',
                'description' => '',
            ],

            /*
             * Age group
             */
            [
                'title' => 'Age group',
                'description' => '',
            ],

            /*
             * Collateral report
             */
            [
                'title' => 'Collateral Report',
                'description' => '',
            ],

            /*
             * Sector report
             */
            [
                'title' => 'Business Sector Report',
                'description' => '',
            ],

            /*
             * Days to Maturity
             */
            [
                'title' => 'Days to Maturity',
                'description' => '',
            ],

            /*
             * Classification Report
             */
            [
                'title' => 'Classification Report',
                'description' => 'Classification of Loans based on their maturity dates',
                'hide' => true
            ],

            /*
             * Early settlement report
             */
            [
                'title' => 'Early settlement report',
                'description' => '',
                'hide' => true
            ],

        ]

    ],
];