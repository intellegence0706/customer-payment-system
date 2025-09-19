<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ProcessMonthlyData::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Process monthly data on the 17th of each month at 2:00 AM
        $schedule->command('monthly:process')
                 ->monthlyOn(17, '02:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Generate monthly reports on the 1st of each month
        $schedule->call(function () {
            // Generate automated monthly reports
            $this->generateMonthlyReports();
        })->monthlyOn(1, '03:00');

        // Backup database daily at 1:00 AM
        $schedule->command('backup:run')
                 ->dailyAt('01:00')
                 ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    private function generateMonthlyReports()
    {
        // Implementation for automated monthly reports
        // This would generate standard reports automatically
    }
}
