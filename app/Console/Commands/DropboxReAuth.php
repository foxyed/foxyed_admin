<?php

namespace App\Console\Commands;

use App\Models\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DropboxReAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropbox:re-auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $item = Settings::byKey("dropbox_refresh_token");
        if (!$item) {
            $this->error("Dropbox Refresh Token not found");
            return;
        }
        $this->info("Using $item as token refresh");
        $response = Http::asForm()
            ->withBasicAuth(
                username: env('DROPBOX_APP_KEY'),
                password: env('DROPBOX_APP_SECRET')
            )
            ->post("https://api.dropbox.com/oauth2/token", [
                'refresh_token' => $item,
                'grant_type' => 'refresh_token',
            ]);
        $ok = Cache::put('dropbox_token', $response['access_token'], \Illuminate\Support\now()->addSeconds($response['expires_in']));
        if(!$ok){
            $this->error("Could not refresh token");
        } else {
            $this->output->success("Token successfully renewed!");
        }

    }
}
