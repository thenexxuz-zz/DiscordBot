<?php

namespace App\Console\Commands;

use Discord\DiscordCommandClient;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\User\Member;
use Discord\Parts\WebSockets\MessageReaction;
use Illuminate\Console\Command;
use function Discord\getColor;

class Poll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll';

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
                $discordClient->registerCommand('poll', function (Message $message) use ($discordClient) {
                    $command = strtolower(substr($message->content, 6));
                    if (is_numeric(substr($command, '0', '1'))) {
                        $pollNum = explode(' ', $command);

                        $message->react('🤷')->done(function () use ($message, $pollNum) {
                            $message->react((intval($pollNum[0]) > 9) ? '🔟' : '🤷')->done(function () use ($message, $pollNum) {
                                $message->react((intval($pollNum[0]) > 8) ? '9️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                    $message->react((intval($pollNum[0]) > 7) ? '8️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                        $message->react((intval($pollNum[0]) > 6) ? '7️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                            $message->react((intval($pollNum[0]) > 5) ? '6️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                                $message->react((intval($pollNum[0]) > 4) ? '5️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                                    $message->react((intval($pollNum[0]) > 3) ? '4️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                                        $message->react((intval($pollNum[0]) > 2) ? '3️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                                            $message->react((intval($pollNum[0]) > 1) ? '2️⃣' : '🤷')->done(function () use ($message, $pollNum) {
                                                                if (intval($pollNum[0]) >= 1) {
                                                                    $message->react('1️⃣');
                                                                }
                                                            });
                                                        });
                                                    });
                                                });
                                            });
                                        });
                                    });
                                });
                            });
                        });
                    } else {
                        $message->react('👍')->done(function () use ($message) {
                            $message->react('👎')->done(function () use ($message) {
                                $message->react('🤔')->done(function () use ($message) {
                                    $message->react('🤷');
                                });
                            });
                        });
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
