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
        try {
            $discordClient = new DiscordCommandClient([
                'token' => env('DISCORD_TOKEN'),
                'prefix' => '.',
                'defaultHelpCommand' => false,
            ]);

            try {
                $discordClient->registerCommand('poll', function (Message $message) use ($discordClient) {
                    $command = strtolower(substr($message->content, 6));
                    if (is_numeric(substr($command, '0', '1'))) {
                        $pollNum = explode(' ', $command);

                        switch (intval($pollNum[0])) {
                            case 10: $message->react('🔟');
                            case 9:  $message->react('9️⃣');
                            case 8:  $message->react('8️⃣');
                            case 7:  $message->react('7️⃣');
                            case 6:  $message->react('6️⃣');
                            case 5:  $message->react('5️⃣');
                            case 4:  $message->react('4️⃣');
                            case 3:  $message->react('3️⃣');
                            default:
                            case 2:  $message->react('2️⃣');
                                     $message->react('1️⃣');
                        }
                    } else {
                        if ($command === '?' || $command === '') {
                            $message->reply("Usage: .poll # message\n#: 2-10 optional\n?: this help message");
                        } else {
                            $message->react('👍');
                            $message->react('👎');
                            $message->react('🤔');
                            $message->react('🤷');
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
