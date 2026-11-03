<?php
$p1 = 'topmostSubform[0].Page1[0].';
$p2 = 'topmostSubform[0].Page2[0].';

return [
    'text' => [
        'surname'            => $p1 . 'T2[0]',
        'given_name'         => $p1 . 'T7[0]',
        'other_names'        => $p1 . 'T16[1]',            // confirm with test
        'dob'                => $p1 . '#area[4].T14[0]',
        'place_of_birth'     => $p1 . '#area[4].T16[0]',
        'nationality'        => $p1 . 'T50[0]',            // confirm; value like "IDN"
        'former_nationality' => $p1 . 'T34[0]',
        'id_no'              => $p1 . 'T37[0]',
        'passport_no'        => $p1 . 'T49[0]',
        'passport_place'     => $p1 . '#area[9].T57[1]',
        'passport_issued'    => $p1 . '#area[9].T53[0]',
        'passport_authority' => $p1 . '#area[0].T57[0]',
        'passport_expiry'    => $p1 . '#area[0].T59[0]',
        'coe_no'             => $p1 . 'T62[0]',
        'purpose'            => $p1 . 'T68[2]',
        'length_of_stay'     => $p1 . 'T68[3]',
        'arrival_date'       => $p1 . 'T66[0]',
        'port_of_entry'      => $p1 . '#area[10].T68[0]',
        'airline'            => $p1 . '#area[10].T68[1]',
        'previous_stays'     => $p1 . 'T64[0]',
        'stay_name'          => $p1 . '#area[12].emp_name[1]',
        'stay_tel'           => $p1 . '#area[12].emp_tel[1]',
        'stay_address'       => $p1 . 'emp_adr[1]',
        'home_address'       => $p1 . 'T0[1]',
        'home_tel'           => $p1 . '#area[11].T97[0]',
        'mobile'             => $p1 . '#area[11].T3[0]',
        'email'              => $p1 . 'T3[1]',
        'occupation'         => $p1 . 'T5[0]',
        'employer_name'      => $p1 . '#area[3].emp_name[0]',
        'employer_tel'       => $p1 . '#area[3].emp_tel[0]',
        'employer_address'   => $p1 . 'emp_adr[0]',
        'guarantor_name'     => $p2 . '#area[0].guarantor_name[0]',
        'guarantor_tel'      => $p2 . '#area[0].guarantor_tel[0]',
        'guarantor_address'  => $p2 . 'guarantor_adr[0]',
        'inviter_name'       => $p2 . '#area[2].T19[0]',
    ],
    'radio' => [
        'sex'           => $p1 . '#area[5].#area[6].#area[7].RB1[0]',
        'marital'       => $p1 . '#area[8].RB2[0]',
        'guarantor_sex' => $p2 . 'RB1[0]',
        'passport_type' => $p1 . '#area[1].#area[2].RB3[0]',
    ],
    'yes_no' => [ // the six "Have you ever" questions
        $p2 . '#area[4].RB5[0]',
        $p2 . '#area[5].RB5[1]',
        $p2 . '#area[6].RB5[2]',
        $p2 . '#area[7].#area[8].RB5[3]',
        $p2 . '#area[9].RB5[4]',
        $p2 . 'RB5[5]',
    ],
    'marital_values'       => ['single' => '1', 'married' => '2', 'widowed' => '3', 'divorced' => '4'],
    'passport_type_values' => ['diplomatic' => '1', 'official' => '2', 'ordinary' => '3', 'other' => '4'],
];
