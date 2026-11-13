<?php

namespace App\Support;

/**
 * Static mock data for the admin UI phase. No database or API involved.
 */
class Mock
{
    public static function tenants(): array
    {
        $rows = [
            ['Anand Verma', 'Gyan Deep Library', 'anand@gyandeep.in', '98250 11234', 'Ahmedabad', 'Pro', 'active', 4, 612],
            ['Meera Shah', 'Saraswati Study Hub', 'meera@saraswatihub.in', '98791 55102', 'Surat', 'Growth', 'active', 2, 284],
            ['Rohit Patel', 'Scholars Corner', 'rohit@scholarscorner.in', '99099 20345', 'Vadodara', 'Starter', 'active', 1, 96],
            ['Kavita Joshi', 'Pustak Reading Lounge', 'kavita@pustaklounge.in', '97144 88021', 'Rajkot', 'Growth', 'trial', 2, 131],
            ['Sanjay Desai', 'Focus Study Zone', 'sanjay@focuszone.in', '98240 67788', 'Gandhinagar', 'Pro', 'active', 5, 745],
            ['Nisha Trivedi', 'Quiet Pages Library', 'nisha@quietpages.in', '90337 12908', 'Bhavnagar', 'Starter', 'suspended', 1, 54],
            ['Harsh Mehta', 'Knowledge Point', 'harsh@knowledgepoint.in', '98980 45512', 'Ahmedabad', 'Growth', 'active', 3, 402],
            ['Pooja Rana', 'Aspire Study Center', 'pooja@aspirecenter.in', '99250 33901', 'Surat', 'Starter', 'trial', 1, 48],
            ['Dhruv Chauhan', 'The Reading Room', 'dhruv@readingroom.in', '98255 77340', 'Jamnagar', 'Starter', 'active', 1, 72],
            ['Ila Pandya', 'Vidya Library & Hall', 'ila@vidyalibrary.in', '97277 10456', 'Mehsana', 'Growth', 'active', 2, 219],
            ['Bhavin Soni', 'Elite Study Hub', 'bhavin@elitehub.in', '98984 22017', 'Vadodara', 'Pro', 'active', 4, 530],
            ['Rina Kapadia', 'Lakshya Reading Hall', 'rina@lakshya.in', '99784 90123', 'Anand', 'Starter', 'expired', 1, 61],
        ];
        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = ['id' => $i + 1, 'owner' => $r[0], 'library' => $r[1], 'email' => $r[2], 'phone' => $r[3], 'city' => $r[4], 'plan' => $r[5], 'status' => $r[6], 'branches' => $r[7], 'students' => $r[8], 'joined' => date('d M Y', strtotime('2025-01-12 +'.($i * 23).' days'))];
        }
        return $out;
    }

    public static function tenant(int $id): array
    {
        return collect(self::tenants())->firstWhere('id', $id) ?? self::tenants()[0];
    }

    public static function plans(): array
    {
        return [
            ['id' => 1, 'name' => 'Starter', 'price' => 1499, 'cycle' => 'Monthly', 'branches' => 1, 'students' => 100, 'tenants' => 38, 'status' => 'active', 'features' => ['1 branch', 'Up to 100 students', 'Seat allocation', 'Email support']],
            ['id' => 2, 'name' => 'Growth', 'price' => 3999, 'cycle' => 'Monthly', 'branches' => 3, 'students' => 500, 'tenants' => 24, 'status' => 'active', 'features' => ['Up to 3 branches', 'Up to 500 students', 'Fee invoices & receipts', 'Attendance tracking', 'Priority support']],
            ['id' => 3, 'name' => 'Pro', 'price' => 8999, 'cycle' => 'Monthly', 'branches' => 10, 'students' => 2500, 'tenants' => 11, 'status' => 'active', 'features' => ['Up to 10 branches', 'Up to 2,500 students', 'Floor plan designer', 'Custom roles', 'Dedicated manager']],
            ['id' => 4, 'name' => 'Growth Annual', 'price' => 39999, 'cycle' => 'Yearly', 'branches' => 3, 'students' => 500, 'tenants' => 7, 'status' => 'active', 'features' => ['Everything in Growth', 'Two months free']],
            ['id' => 5, 'name' => 'Legacy 2024', 'price' => 999, 'cycle' => 'Monthly', 'branches' => 1, 'students' => 60, 'tenants' => 3, 'status' => 'inactive', 'features' => ['1 branch', 'Up to 60 students']],
        ];
    }

    public static function plan(int $id): array
    {
        return collect(self::plans())->firstWhere('id', $id) ?? self::plans()[0];
    }

    public static function branches(): array
    {
        $rows = [
            ['Gyan Deep Library', 'Navrangpura', 'GD-NVR', 'Ahmedabad', 3, 240, 'active'],
            ['Gyan Deep Library', 'Satellite', 'GD-SAT', 'Ahmedabad', 2, 180, 'active'],
            ['Gyan Deep Library', 'Maninagar', 'GD-MAN', 'Ahmedabad', 2, 120, 'active'],
            ['Saraswati Study Hub', 'Adajan', 'SS-ADJ', 'Surat', 2, 150, 'active'],
            ['Saraswati Study Hub', 'Vesu', 'SS-VES', 'Surat', 1, 90, 'active'],
            ['Scholars Corner', 'Alkapuri', 'SC-ALK', 'Vadodara', 1, 80, 'active'],
            ['Focus Study Zone', 'Sector 11', 'FZ-S11', 'Gandhinagar', 2, 160, 'active'],
            ['Focus Study Zone', 'Infocity', 'FZ-INF', 'Gandhinagar', 1, 100, 'inactive'],
            ['Knowledge Point', 'Bopal', 'KP-BPL', 'Ahmedabad', 2, 140, 'active'],
            ['Pustak Reading Lounge', 'Kalawad Road', 'PR-KLW', 'Rajkot', 1, 70, 'active'],
        ];
        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = ['id' => $i + 1, 'tenant' => $r[0], 'name' => $r[1], 'code' => $r[2], 'city' => $r[3], 'halls' => $r[4], 'seats' => $r[5], 'students' => (int) ($r[5] * (0.55 + (($i * 7) % 4) / 10)), 'phone' => '079 4012 '.(3400 + $i * 17), 'status' => $r[6]];
        }
        return $out;
    }

    public static function branch(int $id): array
    {
        return collect(self::branches())->firstWhere('id', $id) ?? self::branches()[0];
    }

    public static function halls(): array
    {
        $rows = [
            ['Silent Hall A', 'Navrangpura', 'Gyan Deep Library', 60, 48, 'active'],
            ['Silent Hall B', 'Navrangpura', 'Gyan Deep Library', 60, 52, 'active'],
            ['Discussion Room', 'Navrangpura', 'Gyan Deep Library', 20, 9, 'active'],
            ['Ground Floor Hall', 'Satellite', 'Gyan Deep Library', 80, 61, 'active'],
            ['AC Hall 1', 'Satellite', 'Gyan Deep Library', 100, 74, 'active'],
            ['Reading Hall', 'Adajan', 'Saraswati Study Hub', 75, 58, 'active'],
            ['Premium Cabins', 'Adajan', 'Saraswati Study Hub', 24, 24, 'active'],
            ['Main Hall', 'Alkapuri', 'Scholars Corner', 80, 37, 'active'],
            ['Upper Floor Hall', 'Sector 11', 'Focus Study Zone', 90, 70, 'maintenance'],
            ['Hall 2', 'Sector 11', 'Focus Study Zone', 70, 41, 'active'],
        ];
        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = ['id' => $i + 1, 'name' => $r[0], 'branch' => $r[1], 'tenant' => $r[2], 'seats' => $r[3], 'occupied' => $r[4], 'floors' => $i % 3 == 0 ? 2 : 1, 'status' => $r[5]];
        }
        return $out;
    }

    public static function hall(int $id): array
    {
        return collect(self::halls())->firstWhere('id', $id) ?? self::halls()[0];
    }

    public static function staff(): array
    {
        $rows = [
            ['Ritesh Parmar', 'Branch Manager', 'Navrangpura', 'Manager'],
            ['Dipika Solanki', 'Front Desk', 'Navrangpura', 'Receptionist'],
            ['Jignesh Bhatt', 'Accountant', 'Satellite', 'Accountant'],
            ['Komal Vyas', 'Librarian', 'Satellite', 'Librarian'],
            ['Manoj Rathod', 'Branch Manager', 'Adajan', 'Manager'],
            ['Sneha Gajjar', 'Front Desk', 'Adajan', 'Receptionist'],
            ['Tejas Modi', 'Housekeeping Lead', 'Alkapuri', 'Support'],
            ['Payal Dave', 'Accountant', 'Sector 11', 'Accountant'],
            ['Chirag Amin', 'Branch Manager', 'Sector 11', 'Manager'],
            ['Hetal Nayak', 'Front Desk', 'Bopal', 'Receptionist'],
        ];
        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = ['id' => $i + 1, 'code' => 'EMP-'.str_pad((string) (101 + $i), 4, '0', STR_PAD_LEFT), 'name' => $r[0], 'email' => strtolower(explode(' ', $r[0])[0]).'@DivyeshCodes.test', 'phone' => '98'.rand(10, 99).' '.rand(10000, 99999), 'designation' => $r[1], 'branch' => $r[2], 'role' => $r[3], 'joined' => date('d M Y', strtotime('2024-02-05 +'.($i * 41).' days')), 'status' => $i == 6 ? 'inactive' : 'active'];
        }
        return $out;
    }

    public static function staffMember(int $id): array
    {
        return collect(self::staff())->firstWhere('id', $id) ?? self::staff()[0];
    }

    public static function students(): array
    {
        $names = ['Aarav Shah', 'Isha Patel', 'Kunal Mehta', 'Riya Desai', 'Yash Trivedi', 'Anjali Joshi', 'Vivek Parikh', 'Nidhi Bhatt', 'Parth Sharma', 'Khushi Thakkar', 'Dev Panchal', 'Mansi Rao', 'Harshil Gandhi', 'Sakshi Pandey', 'Jay Chavda', 'Tanvi Kothari', 'Om Vaghela', 'Zeel Doshi', 'Raj Makwana', 'Foram Brahmbhatt', 'Smit Raval', 'Drashti Oza', 'Nikhil Dave', 'Bhumi Soni'];
        $branches = ['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11', 'Bopal'];
        $shifts = ['Morning (6–12)', 'Afternoon (12–6)', 'Evening (6–11)', 'Full Day'];
        $status = ['active', 'active', 'active', 'due', 'active', 'inactive', 'active', 'due'];
        $out = [];
        foreach ($names as $i => $n) {
            $out[] = ['id' => $i + 1, 'code' => 'STU-'.str_pad((string) (2001 + $i), 5, '0', STR_PAD_LEFT), 'name' => $n, 'phone' => '97'.(10 + $i).' '.(30000 + $i * 731), 'branch' => $branches[$i % 6], 'shift' => $shifts[$i % 4], 'seat' => chr(65 + $i % 4).'-'.(10 + $i * 2), 'fee' => [1200, 1500, 1800, 2500][$i % 4], 'admitted' => date('d M Y', strtotime('2025-03-02 +'.($i * 17).' days')), 'status' => $status[$i % 8]];
        }
        return $out;
    }

    public static function student(int $id): array
    {
        return collect(self::students())->firstWhere('id', $id) ?? self::students()[0];
    }

    public static function feeStructures(): array
    {
        return [
            ['id' => 1, 'name' => 'Morning Monthly', 'branch' => 'Navrangpura', 'shift' => 'Morning (6–12)', 'amount' => 1200, 'duration' => 'Monthly', 'status' => 'active'],
            ['id' => 2, 'name' => 'Afternoon Monthly', 'branch' => 'Navrangpura', 'shift' => 'Afternoon (12–6)', 'amount' => 1200, 'duration' => 'Monthly', 'status' => 'active'],
            ['id' => 3, 'name' => 'Evening Monthly', 'branch' => 'Navrangpura', 'shift' => 'Evening (6–11)', 'amount' => 1500, 'duration' => 'Monthly', 'status' => 'active'],
            ['id' => 4, 'name' => 'Full Day Monthly', 'branch' => 'Navrangpura', 'shift' => 'Full Day', 'amount' => 2500, 'duration' => 'Monthly', 'status' => 'active'],
            ['id' => 5, 'name' => 'Full Day Quarterly', 'branch' => 'Satellite', 'shift' => 'Full Day', 'amount' => 6900, 'duration' => 'Quarterly', 'status' => 'active'],
            ['id' => 6, 'name' => 'Day Pass', 'branch' => 'Satellite', 'shift' => 'Full Day', 'amount' => 100, 'duration' => 'Daily', 'status' => 'active'],
            ['id' => 7, 'name' => 'Weekly Trial', 'branch' => 'Adajan', 'shift' => 'Morning (6–12)', 'amount' => 350, 'duration' => 'Weekly', 'status' => 'inactive'],
            ['id' => 8, 'name' => 'Annual Full Day', 'branch' => 'Adajan', 'shift' => 'Full Day', 'amount' => 24000, 'duration' => 'Yearly', 'status' => 'active'],
        ];
    }

    public static function roles(): array
    {
        return [
            ['id' => 1, 'name' => 'Owner', 'desc' => 'Full access to every module and setting', 'users' => 1, 'system' => true],
            ['id' => 2, 'name' => 'Manager', 'desc' => 'Runs a branch: students, staff, halls and fees', 'users' => 6, 'system' => false],
            ['id' => 3, 'name' => 'Accountant', 'desc' => 'Fee invoices, payments and reports', 'users' => 4, 'system' => false],
            ['id' => 4, 'name' => 'Receptionist', 'desc' => 'Admissions, attendance and seat allocation', 'users' => 9, 'system' => false],
            ['id' => 5, 'name' => 'Librarian', 'desc' => 'Read-only access to students and halls', 'users' => 3, 'system' => false],
        ];
    }

    public static function permissionModules(): array
    {
        return ['Tenants', 'Plans', 'Branches', 'Halls', 'Staff', 'Students', 'Fees', 'Settings'];
    }

    public static function permissionActions(): array
    {
        return ['view', 'create', 'edit', 'delete'];
    }

    /** Default granted permissions per role id: module => actions */
    public static function rolePermissions(int $roleId): array
    {
        $all = self::permissionActions();
        $mods = self::permissionModules();
        $map = [];
        foreach ($mods as $m) {
            $map[$m] = match ($roleId) {
                1 => $all,
                2 => in_array($m, ['Tenants', 'Plans', 'Settings']) ? ['view'] : $all,
                3 => $m === 'Fees' ? $all : (in_array($m, ['Students', 'Branches']) ? ['view'] : []),
                4 => in_array($m, ['Students', 'Halls']) ? ['view', 'create', 'edit'] : (in_array($m, ['Branches']) ? ['view'] : []),
                default => in_array($m, ['Students', 'Halls', 'Branches']) ? ['view'] : [],
            };
        }
        return $map;
    }

    public static function activities(): array
    {
        return [
            ['Rina Kapadia’s plan expired', 'Lakshya Reading Hall · Starter', '12 min ago', 'warn'],
            ['New tenant registered', 'Aspire Study Center started a 14-day trial', '1 hr ago', 'info'],
            ['Plan upgraded to Pro', 'Elite Study Hub · ₹8,999 / month', '3 hrs ago', 'ok'],
            ['Branch added', 'Focus Study Zone · Infocity (FZ-INF)', 'Yesterday, 6:40 PM', 'info'],
            ['Tenant suspended', 'Quiet Pages Library · overdue subscription', 'Yesterday, 11:15 AM', 'bad'],
            ['Payment received', 'Gyan Deep Library · ₹8,999', '2 days ago', 'ok'],
            ['Hall marked for maintenance', 'Focus Study Zone · Upper Floor Hall', '2 days ago', 'warn'],
        ];
    }

    public static function months(): array
    {
        return ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];
    }
}
