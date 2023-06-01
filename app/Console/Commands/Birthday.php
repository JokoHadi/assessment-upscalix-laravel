<?php

namespace App\Console\Commands;

use App\Models\BirthdayMessageLog;
use App\Models\MessageTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Birthday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Message';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::orderBy("timezone")->chunk(100, function($users)
        {
            foreach ($users as $user)
            {
                $now = now()->setTimezone($user->timezone);
                $check = BirthdayMessageLog::where("user_id", $user->id)->whereYear("created_at", $now->year);
                if($check->exists()){
                    continue;
                }

                $userDob = Carbon::create($user->dob);
                $sendOn = Carbon::createFromFormat("Y-m-d H:i:s", $now->year. "-" . $userDob->month . "-" . $userDob->day . " 09:00:00", $user->timezone);

                if($now->gte($sendOn)){
                    $template = MessageTemplate::where("label", "BIRTHDAY")->value("message");
                    $message = Str::replace('{full_name}', $user->full_name, $template);
                    $log = BirthdayMessageLog::create([
                        "user_id" => $user->id,
                        "message" => $message,
                        "status" => 0,
                    ])->fresh();

                    try {
                        $response = Http::post("https://email-service.digitalenvision.com.au/send-email", [
                            "email" => $user->email,
                            "message" => $message
                        ]);
                        if($response->ok()){
                            $log->update([
                                "status" => 1
                            ]);
                        }
                    } catch (\Throwable $th) {
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
