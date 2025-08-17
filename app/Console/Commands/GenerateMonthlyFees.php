<?php

namespace App\Console\Commands;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly fees automatically for all students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $yearMonth = now()->format('F Y'); 

            $students = Student::all();

            foreach ($students as $student) {
                // Check already exists
                $exists = Fee::where('student_id', $student->id)
                    ->where('year_month', $yearMonth)
                    ->exists();

                if (!$exists) {
                    Fee::create([
                        'tuition_id'   => $student->tuition_id,
                        'student_id'   => $student->id,
                        'monthly_fees' => $student->monthly_fees ?? 0,
                        'year_month'   => $yearMonth,
                        'is_paid'      => false,
                    ]);
                }
            }

            DB::commit();

            $this->info("Monthly fees generated for {$yearMonth}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }
}
