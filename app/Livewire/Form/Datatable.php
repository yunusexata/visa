<?php

namespace App\Livewire\Form;

use Livewire\Component;

class Datatable extends Component
{
    public function getOrdersProperty()
    {
        return collect([
            (object)[
                'id' => 'ORD-1001',
                'customer_name' => 'Siti Aminah',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-05-12',
                'status' => 'Active',
            ],
            (object)[
                'id' => 'ORD-1002',
                'customer_name' => 'Budi Santoso',
                'lpk' => 'LPK Nihon Go',
                'package' => 'Magang (Jisshusei)',
                'departure_date' => '2026-06-20',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1003',
                'customer_name' => 'Dewi Lestari',
                'lpk' => 'LPK Asahi Indonesia',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-04-15',
                'status' => 'Completed',
            ],
            (object)[
                'id' => 'ORD-1004',
                'customer_name' => 'Ahmad Fauzi',
                'lpk' => 'LPK Bunkasai',
                'package' => 'Student Visa',
                'departure_date' => '2026-07-01',
                'status' => 'Processing',
            ],
            (object)[
                'id' => 'ORD-1005',
                'customer_name' => 'Rina Melati',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-08-10',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1001',
                'customer_name' => 'Siti Aminah',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-05-12',
                'status' => 'Active',
            ],
            (object)[
                'id' => 'ORD-1002',
                'customer_name' => 'Budi Santoso',
                'lpk' => 'LPK Nihon Go',
                'package' => 'Magang (Jisshusei)',
                'departure_date' => '2026-06-20',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1003',
                'customer_name' => 'Dewi Lestari',
                'lpk' => 'LPK Asahi Indonesia',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-04-15',
                'status' => 'Completed',
            ],
            (object)[
                'id' => 'ORD-1004',
                'customer_name' => 'Ahmad Fauzi',
                'lpk' => 'LPK Bunkasai',
                'package' => 'Student Visa',
                'departure_date' => '2026-07-01',
                'status' => 'Processing',
            ],
            (object)[
                'id' => 'ORD-1005',
                'customer_name' => 'Rina Melati',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-08-10',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1001',
                'customer_name' => 'Siti Aminah',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-05-12',
                'status' => 'Active',
            ],
            (object)[
                'id' => 'ORD-1002',
                'customer_name' => 'Budi Santoso',
                'lpk' => 'LPK Nihon Go',
                'package' => 'Magang (Jisshusei)',
                'departure_date' => '2026-06-20',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1003',
                'customer_name' => 'Dewi Lestari',
                'lpk' => 'LPK Asahi Indonesia',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-04-15',
                'status' => 'Completed',
            ],
            (object)[
                'id' => 'ORD-1004',
                'customer_name' => 'Ahmad Fauzi',
                'lpk' => 'LPK Bunkasai',
                'package' => 'Student Visa',
                'departure_date' => '2026-07-01',
                'status' => 'Processing',
            ],
            (object)[
                'id' => 'ORD-1005',
                'customer_name' => 'Rina Melati',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-08-10',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1001',
                'customer_name' => 'Siti Aminah',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-05-12',
                'status' => 'Active',
            ],
            (object)[
                'id' => 'ORD-1002',
                'customer_name' => 'Budi Santoso',
                'lpk' => 'LPK Nihon Go',
                'package' => 'Magang (Jisshusei)',
                'departure_date' => '2026-06-20',
                'status' => 'Pending',
            ],
            (object)[
                'id' => 'ORD-1003',
                'customer_name' => 'Dewi Lestari',
                'lpk' => 'LPK Asahi Indonesia',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-04-15',
                'status' => 'Completed',
            ],
            (object)[
                'id' => 'ORD-1004',
                'customer_name' => 'Ahmad Fauzi',
                'lpk' => 'LPK Bunkasai',
                'package' => 'Student Visa',
                'departure_date' => '2026-07-01',
                'status' => 'Processing',
            ],
            (object)[
                'id' => 'ORD-1005',
                'customer_name' => 'Rina Melati',
                'lpk' => 'LPK Sakura Mandiri',
                'package' => 'Tokutei Ginou (SSW)',
                'departure_date' => '2026-08-10',
                'status' => 'Pending',
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.form.datatable');
    }
}
