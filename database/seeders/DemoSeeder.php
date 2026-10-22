<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\SickSheet;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data so no screen is empty during your presentation.
 * Every account's password is:  password
 *
 *   admin    admin@mubs.ac.ug
 *   doctor   g.namutebi@mubs.ac.ug
 *   student  amina.nakato@std.mubs.ac.ug
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026);                       // same "random" demo data every run
        $hash = Hash::make('password');

        // ---------------- Admin ----------------
        User::create([
            'name' => 'System Admin', 'email' => 'admin@mubs.ac.ug',
            'password' => $hash, 'role' => 'admin',
        ]);

        // ---------------- Doctors ----------------
        $doctorData = [
            ['Grace Namutebi', 'g.namutebi@mubs.ac.ug', 'General Practitioner', 'Room 101', '0700111222'],
            ['Peter Okello',   'p.okello@mubs.ac.ug',   'Dentist',              'Room 102', '0700333444'],
            ['Sarah Atim',     's.atim@mubs.ac.ug',     'Counsellor',           'Room 103', '0700555666'],
        ];
        $doctors = [];
        foreach ($doctorData as [$name, $email, $spec, $room, $phone]) {
            $user = User::create(['name' => $name, 'email' => $email, 'password' => $hash, 'role' => 'doctor']);
            $doctors[] = Doctor::create([
                'user_id' => $user->id, 'specialization' => $spec, 'room_number' => $room, 'phone' => $phone,
            ]);
        } 

        // ---------------- Schedules (same hours the booking page mock uses) ----------------
        $schedules = [
            0 => [['Monday', '09:00', '16:00'], ['Wednesday', '09:00', '16:00'], ['Friday', '09:00', '13:00']],
            1 => [['Tuesday', '10:00', '15:00'], ['Thursday', '10:00', '15:00']],
            2 => [['Tuesday', '09:00', '13:00'], ['Thursday', '09:00', '13:00']],
        ];
        foreach ($schedules as $i => $blocks) {
            foreach ($blocks as [$day, $start, $end]) {
                DoctorSchedule::create([
                    'doctor_id' => $doctors[$i]->id, 'day_of_week' => $day,
                    'start_time' => $start, 'end_time' => $end, 'slot_duration_minutes' => 30,
                ]);
            }
        }

        // ---------------- Students ----------------
        $studentData = [
            ['Amina Nakato',     'amina.nakato@std.mubs.ac.ug',     '22/BSC/CS/001',  'BSc. Computer Science',                'Female', '0781234567'],
            ['Brian Mugisha',    'brian.mugisha@std.mubs.ac.ug',    '22/BBA/GEN/045', 'Bachelor of Business Administration',  'Male',   '0789876543'],
            ['Carol Atuhaire',   'carol.atuhaire@std.mubs.ac.ug',   '21/BAF/ACC/112', 'Bachelor of Accounting and Finance',   'Female', '0772345678'],
            ['David Ssemakula',  'david.ssemakula@std.mubs.ac.ug',  '23/BPL/PRO/027', 'BSc. Procurement and Logistics',       'Male',   '0755123987'],
            ['Esther Nabirye',   'esther.nabirye@std.mubs.ac.ug',   '22/BMK/MKT/076', 'Bachelor of Marketing',                'Female', '0703456123'],
            ['Felix Opio',       'felix.opio@std.mubs.ac.ug',       '21/BIT/BIT/009', 'BSc. Business Computing',              'Male',   '0788654321'],
            ['Irene Kemigisha',  'irene.kemigisha@std.mubs.ac.ug',  '23/BHR/HRM/058', 'Bachelor of Human Resource Management','Female', '0712987456'],
            ['Hassan Kato',      'hassan.kato@std.mubs.ac.ug',      '22/BEN/ENT/033', 'Bachelor of Entrepreneurship',         'Male',   '0779001122'],
        ];
        $students = [];
        foreach ($studentData as [$name, $email, $reg, $course, $gender, $phone]) {
            $user = User::create(['name' => $name, 'email' => $email, 'password' => $hash, 'role' => 'student']);
            $students[] = Student::create([
                'user_id' => $user->id, 'reg_number' => $reg, 'course' => $course,
                'gender' => $gender, 'phone' => $phone,
            ]);
        }

        // ---------------- Clinical sample text (fictional) ----------------
        $clinical = [
            'General Practitioner' => [
                ['Malaria', 'Antimalarial course (3 days)'],
                ['Flu / common cold', 'Paracetamol, fluids and rest'],
                ['Tension headache', 'Paracetamol, hydration, rest'],
                ['Stomach ulcer', 'Antacid course (14 days)'],
                ['Typhoid', 'Antibiotic course (7 days)'],
                ['Allergic rhinitis', 'Antihistamine at night'],
            ],
            'Dentist' => [
                ['Dental caries', 'Filling booked; pain relief as needed'],
                ['Gingivitis', 'Antiseptic mouthwash twice daily'],
            ],
            'Counsellor' => [
                ['Exam-related stress', 'Counselling sessions; sleep routine advice'],
            ],
        ];
        $categoryFor = [
            'General Practitioner' => 'General Consultation',
            'Dentist' => 'Dental Checkup',
            'Counsellor' => 'Counseling',
        ];

        // ---------------- Upcoming appointments (match the screens in the UI) ----------------
        $upcoming = [
            [0, 0, 'next monday',    '09:30:00', 'pending',  'Recurring headaches'],
            [1, 1, 'next tuesday',   '10:30:00', 'approved', 'Toothache'],
            [2, 0, 'next wednesday', '10:00:00', 'approved', 'Follow-up visit'],
            [3, 1, 'next thursday',  '11:00:00', 'pending',  'Gum bleeding'],
            [4, 2, 'next tuesday',   '09:30:00', 'pending',  'Exam stress'],
        ];
        foreach ($upcoming as [$s, $d, $when, $slot, $status, $reason]) {
            Appointment::create([
                'student_id' => $students[$s]->id, 'doctor_id' => $doctors[$d]->id,
                'category' => $categoryFor[$doctors[$d]->specialization],
                'date' => Carbon::parse($when)->toDateString(), 'time_slot' => $slot,
                'status' => $status, 'reason' => $reason,
            ]);
        }

        // One cancelled booking so every status badge appears on Amina's dashboard
        Appointment::create([
            'student_id' => $students[0]->id, 'doctor_id' => $doctors[0]->id,
            'category' => 'Eye Checkup', 'date' => Carbon::parse('last friday')->toDateString(),
            'time_slot' => '11:00:00', 'status' => 'cancelled', 'reason' => 'Blurred vision',
        ]);

        // ---------------- A month of history (feeds the analytics charts) ----------------
        for ($daysAgo = 28; $daysAgo >= 1; $daysAgo--) {
            $date = now()->subDays($daysAgo)->startOfDay();

            foreach ($doctors as $doctor) {
                $blocks = DoctorSchedule::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $date->englishDayOfWeek)->get();

                foreach ($blocks as $block) {
                    $slots = $this->slots(substr($block->start_time, 0, 5), substr($block->end_time, 0, 5));
                    shuffle($slots);

                    foreach (array_slice($slots, 0, mt_rand(2, 5)) as $slot) {
                        $student = $students[mt_rand(0, count($students) - 1)];
                        $cancelled = mt_rand(1, 10) === 1;

                        $appointment = Appointment::create([
                            'student_id' => $student->id, 'doctor_id' => $doctor->id,
                            'category' => $categoryFor[$doctor->specialization],
                            'date' => $date->toDateString(), 'time_slot' => $slot,
                            'status' => $cancelled ? 'cancelled' : 'completed',
                            'reason' => 'Routine visit',
                        ]);
                        if ($cancelled) {
                            continue;
                        }

                        $pool = $clinical[$doctor->specialization];
                        [$diagnosis, $prescription] = $pool[mt_rand(0, count($pool) - 1)];

                        $when = $date->copy()->setTimeFromTimeString($slot)->addMinutes(20);
                        (new MedicalRecord())->forceFill([
                            'appointment_id' => $appointment->id, 'student_id' => $student->id,
                            'doctor_id' => $doctor->id, 'diagnosis' => $diagnosis,
                            'prescription' => $prescription, 'notes' => null,
                            'created_at' => $when, 'updated_at' => $when,
                        ])->save();

                        // Roughly 1 in 4 general consultations also gets a sick sheet
                        if ($doctor->specialization === 'General Practitioner' && mt_rand(1, 4) === 1) {
                            (new SickSheet())->forceFill([
                                'appointment_id' => $appointment->id, 'student_id' => $student->id,
                                'doctor_id' => $doctor->id, 'reference_no' => SickSheet::nextReference(),
                                'rest_from' => $date->toDateString(),
                                'rest_to' => $date->copy()->addDays(2)->toDateString(),
                                'remarks' => 'Advised rest and hydration.',
                                'created_at' => $when, 'updated_at' => $when,
                            ])->save();
                        }
                    }
                }
            }
        }
    }

    /** Builds time slots, e.g. slots('09:00','10:00') => ['09:00:00','09:30:00'] */
    private function slots(string $start, string $end, int $minutes = 30): array
    {
        $slots = [];
        $t = Carbon::createFromFormat('H:i', $start);
        $limit = Carbon::createFromFormat('H:i', $end);

        while ($t->copy()->addMinutes($minutes) <= $limit) {
            $slots[] = $t->format('H:i:s');
            $t->addMinutes($minutes);
        }
        return $slots;
    }
}
