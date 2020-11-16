<?php

namespace App\Console\Commands;

use Discord\DiscordCommandClient;
use Illuminate\Console\Command;

class LogChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Logging for Discord';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $discordClient = null;
        try {
            $discordClient = new DiscordCommandClient([
                'token' => env('DISCORD_TOKEN'),
                'prefix' => '.',
            ]);
        }
        catch (\Exception $exception) {
            echo "Cannot connect to Discord.".PHP_EOL;
        }

        if (!is_null($discordClient)) {
            $discordClient->on('ready', function($discord) {
                $discord->on('message', function($message) {
                    $text = date("Y-m-d H:i:s") . ' ' . $message->author->username. ': ' . $message->content . PHP_EOL;
                    file_put_contents(storage_path("logs/{$message->channel->name}.log"), $text , FILE_APPEND | LOCK_EX);
                });
            });

            $discordClient->run();
        }
        return 0;
    }
}
