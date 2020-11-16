<?php

namespace App\Console\Commands;

use Discord\DiscordCommandClient;
use Discord\Parts\User\Member;
use Illuminate\Console\Command;

class Rules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:rules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rules for Discord';

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

            try {
                $discordClient->registerCommand('rules', function ($message) use ($discordClient) {
                    if ($message->channel->name === 'bot-commands') {
                        if (substr($message->content, 7) === strtolower('agree')) {
                            $guild = $discordClient->guilds->get('id', env('DISCORD_GUILD_ID'));
                            $guild->members->fetch($message->author->id)
                                ->done(function (Member $member) use ($message) {
                                    $member->addRole("775042190761525299")->done(function () use ($message) {
                                        $message->reply('welcome!');
                                    }, function ($e) use ($message) {
                                        $message->reply("you're already a member.");
                                    });
                                });
                        } else {
                            $message->reply('you need to "agree".');
                        }
                    }
                });
            } catch (\Exception $exception) {
                print_r($exception->getMessage());
            }

            $discordClient->run();
        } catch (\Exception $exception) {
            print_r($exception->getMessage() . PHP_EOL);
            echo "Cannot connect to Discord." . PHP_EOL;
        }

        return 0;
    }
}
