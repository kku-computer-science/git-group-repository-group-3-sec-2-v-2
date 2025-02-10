<?php
   
namespace App\Console;
    
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
    
class Kernel extends ConsoleKernel
{
    /**
     * คำสั่ง Artisan ที่แอปพลิเคชันของคุณให้บริการ
     *
     * @var array
     */
    protected $commands = [
        Commands\Scopus::class,
    ];
     
    /**
     * กำหนดตารางเวลาของคำสั่งแอปพลิเคชัน
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // กำหนดให้คำสั่ง demo:cron ทำงานตามเวลาที่กำหนด
        #$schedule->command('demo:cron')->cron('0 0 15 2,5,8,11 *')->timezone('Asia/Bangkok');
        // $schedule->command('scopus:cron')
        //  ->at('02:59')
        //  ->timezone('Asia/Bangkok')
        //  ->appendOutputTo(storage_path('logs/demo_cron.log'));

        //$schedule->command('demo:cron')->cron('58 15 20 2,4,8,11 *')->timezone('Asia/Bangkok');
        $schedule->command('scopus:fetch')
        ->daily()  // หรือกำหนดเวลาอื่นๆ ตามต้องการ
        ->at('01:00')
        ->appendOutputTo(storage_path('logs/scopus-cron.log'));
    }
     
    /**
     * ลงทะเบียนคำสั่งสำหรับแอปพลิเคชัน
     *
     * @return void
     */
    protected function commands()
    {
        // โหลดคำสั่งจากไดเรกทอรี Commands
        $this->load(__DIR__.'/Commands');
     
        // รวมไฟล์ console.php ที่อยู่ในไดเรกทอรี routes
        require base_path('routes/console.php');
    }
}