<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExampleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['first_name' => 'Alice',   'last_name' => 'Anderson',  'email' => 'alice.anderson@example.com',   'role' => 'Admin',  'status' => 'Active',   'joined' => '2023-01-15'],
            ['first_name' => 'Bob',     'last_name' => 'Baker',     'email' => 'bob.baker@example.com',        'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-02-03'],
            ['first_name' => 'Carol',   'last_name' => 'Clark',     'email' => 'carol.clark@example.com',      'role' => 'Viewer', 'status' => 'Inactive', 'joined' => '2023-02-18'],
            ['first_name' => 'David',   'last_name' => 'Davis',     'email' => 'david.davis@example.com',      'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-03-07'],
            ['first_name' => 'Eve',     'last_name' => 'Evans',     'email' => 'eve.evans@example.com',        'role' => 'Viewer', 'status' => 'Active',   'joined' => '2023-03-22'],
            ['first_name' => 'Frank',   'last_name' => 'Foster',    'email' => 'frank.foster@example.com',     'role' => 'Editor', 'status' => 'Banned',   'joined' => '2023-04-01'],
            ['first_name' => 'Grace',   'last_name' => 'Green',     'email' => 'grace.green@example.com',      'role' => 'Admin',  'status' => 'Active',   'joined' => '2023-04-14'],
            ['first_name' => 'Henry',   'last_name' => 'Harris',    'email' => 'henry.harris@example.com',     'role' => 'Viewer', 'status' => 'Active',   'joined' => '2023-05-09'],
            ['first_name' => 'Isla',    'last_name' => 'Ingram',    'email' => 'isla.ingram@example.com',      'role' => 'Editor', 'status' => 'Inactive', 'joined' => '2023-05-25'],
            ['first_name' => 'Jack',    'last_name' => 'Jones',     'email' => 'jack.jones@example.com',       'role' => 'Viewer', 'status' => 'Active',   'joined' => '2023-06-02'],
            ['first_name' => 'Karen',   'last_name' => 'King',      'email' => 'karen.king@example.com',       'role' => 'Admin',  'status' => 'Active',   'joined' => '2023-06-18'],
            ['first_name' => 'Liam',    'last_name' => 'Lee',       'email' => 'liam.lee@example.com',         'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-07-04'],
            ['first_name' => 'Mia',     'last_name' => 'Moore',     'email' => 'mia.moore@example.com',        'role' => 'Viewer', 'status' => 'Banned',   'joined' => '2023-07-19'],
            ['first_name' => 'Noah',    'last_name' => 'Nelson',    'email' => 'noah.nelson@example.com',      'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-08-05'],
            ['first_name' => 'Olivia',  'last_name' => 'Owen',      'email' => 'olivia.owen@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2023-08-20'],
            ['first_name' => 'Peter',   'last_name' => 'Parker',    'email' => 'peter.parker@example.com',     'role' => 'Editor', 'status' => 'Inactive', 'joined' => '2023-09-01'],
            ['first_name' => 'Quinn',   'last_name' => 'Quinn',     'email' => 'quinn.quinn@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2023-09-14'],
            ['first_name' => 'Rachel',  'last_name' => 'Reed',      'email' => 'rachel.reed@example.com',      'role' => 'Admin',  'status' => 'Active',   'joined' => '2023-09-28'],
            ['first_name' => 'Sam',     'last_name' => 'Scott',     'email' => 'sam.scott@example.com',        'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-10-10'],
            ['first_name' => 'Tina',    'last_name' => 'Turner',    'email' => 'tina.turner@example.com',      'role' => 'Viewer', 'status' => 'Banned',   'joined' => '2023-10-22'],
            ['first_name' => 'Uma',     'last_name' => 'Underwood', 'email' => 'uma.underwood@example.com',    'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-11-03'],
            ['first_name' => 'Victor',  'last_name' => 'Vance',     'email' => 'victor.vance@example.com',     'role' => 'Viewer', 'status' => 'Active',   'joined' => '2023-11-17'],
            ['first_name' => 'Wendy',   'last_name' => 'Walsh',     'email' => 'wendy.walsh@example.com',      'role' => 'Admin',  'status' => 'Inactive', 'joined' => '2023-12-01'],
            ['first_name' => 'Xander',  'last_name' => 'Xu',        'email' => 'xander.xu@example.com',        'role' => 'Editor', 'status' => 'Active',   'joined' => '2023-12-15'],
            ['first_name' => 'Yara',    'last_name' => 'Young',     'email' => 'yara.young@example.com',       'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-01-07'],
            ['first_name' => 'Zoe',     'last_name' => 'Zhang',     'email' => 'zoe.zhang@example.com',        'role' => 'Editor', 'status' => 'Banned',   'joined' => '2024-01-20'],
            ['first_name' => 'Aaron',   'last_name' => 'Adams',     'email' => 'aaron.adams@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-02-04'],
            ['first_name' => 'Beth',    'last_name' => 'Brown',     'email' => 'beth.brown@example.com',       'role' => 'Editor', 'status' => 'Active',   'joined' => '2024-02-18'],
            ['first_name' => 'Chris',   'last_name' => 'Cooper',    'email' => 'chris.cooper@example.com',     'role' => 'Admin',  'status' => 'Inactive', 'joined' => '2024-03-03'],
            ['first_name' => 'Diana',   'last_name' => 'Diaz',      'email' => 'diana.diaz@example.com',       'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-03-17'],
            ['first_name' => 'Ethan',   'last_name' => 'Ellis',     'email' => 'ethan.ellis@example.com',      'role' => 'Editor', 'status' => 'Active',   'joined' => '2024-04-01'],
            ['first_name' => 'Fiona',   'last_name' => 'Flynn',     'email' => 'fiona.flynn@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-04-14'],
            ['first_name' => 'George',  'last_name' => 'Grant',     'email' => 'george.grant@example.com',     'role' => 'Editor', 'status' => 'Banned',   'joined' => '2024-04-28'],
            ['first_name' => 'Hannah',  'last_name' => 'Hill',      'email' => 'hannah.hill@example.com',      'role' => 'Admin',  'status' => 'Active',   'joined' => '2024-05-10'],
            ['first_name' => 'Ian',     'last_name' => 'Irving',    'email' => 'ian.irving@example.com',       'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-05-24'],
            ['first_name' => 'Julia',   'last_name' => 'James',     'email' => 'julia.james@example.com',      'role' => 'Editor', 'status' => 'Inactive', 'joined' => '2024-06-07'],
            ['first_name' => 'Kyle',    'last_name' => 'Knight',    'email' => 'kyle.knight@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-06-21'],
            ['first_name' => 'Laura',   'last_name' => 'Lane',      'email' => 'laura.lane@example.com',       'role' => 'Editor', 'status' => 'Active',   'joined' => '2024-07-05'],
            ['first_name' => 'Mike',    'last_name' => 'Mills',     'email' => 'mike.mills@example.com',       'role' => 'Admin',  'status' => 'Active',   'joined' => '2024-07-19'],
            ['first_name' => 'Nina',    'last_name' => 'Nash',      'email' => 'nina.nash@example.com',        'role' => 'Viewer', 'status' => 'Banned',   'joined' => '2024-08-02'],
            ['first_name' => 'Oscar',   'last_name' => 'Olsen',     'email' => 'oscar.olsen@example.com',      'role' => 'Editor', 'status' => 'Active',   'joined' => '2024-08-16'],
            ['first_name' => 'Paula',   'last_name' => 'Price',     'email' => 'paula.price@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-08-30'],
            ['first_name' => 'Quentin', 'last_name' => 'Quincy',    'email' => 'quentin.quincy@example.com',   'role' => 'Editor', 'status' => 'Inactive', 'joined' => '2024-09-13'],
            ['first_name' => 'Rosa',    'last_name' => 'Rivera',    'email' => 'rosa.rivera@example.com',      'role' => 'Admin',  'status' => 'Active',   'joined' => '2024-09-27'],
            ['first_name' => 'Steve',   'last_name' => 'Stone',     'email' => 'steve.stone@example.com',      'role' => 'Viewer', 'status' => 'Active',   'joined' => '2024-10-11'],
            ['first_name' => 'Tracy',   'last_name' => 'Todd',      'email' => 'tracy.todd@example.com',       'role' => 'Editor', 'status' => 'Active',   'joined' => '2024-10-25'],
            ['first_name' => 'Ursula',  'last_name' => 'Upton',     'email' => 'ursula.upton@example.com',     'role' => 'Viewer', 'status' => 'Banned',   'joined' => '2024-11-08'],
            ['first_name' => 'Vince',   'last_name' => 'Vaughn',    'email' => 'vince.vaughn@example.com',     'role' => 'Editor', 'status' => 'Active',   'joined' => '2024-11-22'],
            ['first_name' => 'Wilma',   'last_name' => 'Ward',      'email' => 'wilma.ward@example.com',       'role' => 'Admin',  'status' => 'Active',   'joined' => '2024-12-06'],
            ['first_name' => 'Xena',    'last_name' => 'Xavier',    'email' => 'xena.xavier@example.com',      'role' => 'Viewer', 'status' => 'Inactive', 'joined' => '2024-12-20'],
            ['first_name' => 'Yusuf',   'last_name' => 'York',      'email' => 'yusuf.york@example.com',       'role' => 'Editor', 'status' => 'Active',   'joined' => '2025-01-03'],
            ['first_name' => 'Zara',    'last_name' => 'Zimmerman', 'email' => 'zara.zimmerman@example.com',   'role' => 'Viewer', 'status' => 'Active',   'joined' => '2025-01-17'],
        ];

        $this->db->table('example')->insertBatch($data);
    }
}
