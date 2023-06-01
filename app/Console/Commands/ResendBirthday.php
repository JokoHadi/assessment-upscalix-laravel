<?php

namespace App\Console\Commands;

use App\Models\BirthdayMessageLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ResendBirthday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        BirthdayMessageLog::where("status", "!=", 1)->with(["user"])->chunk(100, function ($logs) {
            foreach ($logs as $log) {
                try {
                    $response = Http::post("https://email-service.digitalenvision.com.au/send-email", [
                        "email" => $log->user->email,
                        "message" => $log->message
                    ]);
                    if ($response->ok()) {
                        $log->update([
                            "status" => 1
                        ]);
                    }
                } catch (\Throwable $th) {
                    if ($response->ok()) {
                        $log->update([
                            "status" => 3
                        ]);
                    }
                }
            }
        });
        return Command::SUCCESS;
    }
}
