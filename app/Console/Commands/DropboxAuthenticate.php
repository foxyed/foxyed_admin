<?php

namespace App\Console\Commands;

use App\Models\Settings;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DropboxAuthenticate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropbox:authenticate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws ConnectionException
     */
    public function handle()
    {
        $url = http_build_query([
            'client_id' => env('DROPBOX_APP_KEY'),
            'token_access_type' => 'offline',
            'response_type' => 'code',
        ]);

        $url = "https://www.dropbox.com/oauth2/authorize?" . $url;
        $this->output->info("Please use this link to autenticate: $url");
        $token = $this->ask('What is the access token?');

        $response = Http::asForm()
            ->withBasicAuth(
                username: env('DROPBOX_APP_KEY'),
                password: env('DROPBOX_APP_SECRET')
            )
            ->post("https://api.dropbox.com/oauth2/token", [
                'code' => $token,
                'grant_type' => 'authorization_code',
            ]);

        Cache::put('dropbox_token', $response['access_token'], \Illuminate\Support\now()->addSeconds($response['expires_in']));
        $this->output->success("Token successfully generated!");
        $item = Settings::query()->where([
            'key' => 'dropbox_refresh_token',
        ]);
        if (!$item->exists()) {
            Settings::query()->create([
                'label' => "Dropbox Refresh Token",
                'key' => 'dropbox_refresh_token',
                'value' => $response['refresh_token'],
            ]);
        } else {
            $item = $item->first();
            $item->update([
                'value' => $token,
            ]);
        }
        $this->output->success("Refresh Token successfully stored!");
    }
}
