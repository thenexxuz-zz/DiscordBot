<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Role;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Discord\DiscordCommandClient;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Embed\Image;
use Discord\Parts\Guild\Guild;
use Exception;
use Illuminate\Console\Command;

class Points extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:points';

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
    public function handle(): int
    {
        try {
            $discordClient = new DiscordCommandClient([
                'token' => env('DISCORD_TOKEN'),
                'prefix' => '.',
            ]);

            try {
                $discordClient->registerCommand('points', function (Message $message) use ($discordClient) {
                    $command = strtolower(substr($message->content, 8));
                    if ($command === '') {
                        $this->showHelp($discordClient, $message->channel_id);
                    } else {
                        try {
                            $m = Member::where([
                                'id' => $message->author->id,
                                'guild_id' => $message->guild_id,
                            ])->first();
                            if (is_null($m->id)) {
                                throw new Exception('You are not in the points program');
                            }
                            switch ($command) {
                                case 'bal':
                                case 'balance':
                                    $message->reply('Points: ' . $m->balance);
                                    break;
                                case 'send':

                                    break;
                                case 'shop':
                                    $message->reply('todo');
                                    break;
                                default:
                                    $this->showHelp($discordClient, $message->channel_id);

                            }
                        }
                        catch(Exception $exception) {
                            $message->reply($exception->getMessage());
                            $this->showHelp($discordClient, $message->channel_id);
                        }
                    }
                });
                $discordClient->registerCommand('balance', function (Message $message) use ($discordClient) {
                    try {
                        $m = Member::where([
                            'id' => $message->author->id,
                            'guild_id' => $message->guild_id,
                        ])->first();

                        if (is_null($m->id)) {
                            throw new Exception('You are not in the points program');
                        }
                        $message->reply('Points: ' . $m->balance);
                    }
                    catch(Exception $exception) {
                        $message->reply($exception->getMessage());
                        $this->showHelp($discordClient, $message->channel_id);
                    }
                });
                $discordClient->registerAlias('bal', 'balance');
                $discordClient->registerAlias('$', 'balance');

                $discordClient->registerCommand('send', function (Message $message) use ($discordClient) {
                    $command = strtolower(substr($message->content, 6));
                    if ($command === '') {
                        $this->showHelp($discordClient, $message->channel_id);
                    } else {
                        try {
                            $m = Member::where([
                                'id' => $message->author->id,
                                'guild_id' => $message->guild_id,
                            ])->first();
                            if (is_null($m->id)) {
                                throw new Exception('You are not in the points program');
                            }
                            $args = preg_split("/\s/", $command);
                            if (count($args) !== 2) {
                                throw new Exception('Invalid number of parameters.');
                            }
                            $amount = $args[0];
                            $recipient = $args[1];
                            $recipientId = filter_var($recipient, FILTER_SANITIZE_NUMBER_INT);
                            $r = Member::where([
                                'id' => $recipientId,
                                'guild_id' => $message->guild_id,
                            ])->first();
                            if (is_null($r->id)) {
                                throw new Exception("$recipient is not in the points program.");
                            }
                            if ($m->balance >= $amount) {
                                $m->balance -= $amount;
                                $r->balance += $amount;
                                $m->save();
                                $r->save();
                                $message->reply('Sending ' . $amount . ' to ' . $recipient);
                            } else {
                                throw new Exception('Insufficient points for transfer');
                            }
                        }
                        catch(Exception $exception) {
                            $message->reply($exception->getMessage());
                        }
                    }
                });

                $discordClient->registerCommand('shop', function (Message $message) use ($discordClient) {
                    $r = Role::where(['available'=>true])->get();
                    $roles = [];
                    foreach ($r as $role) {
                        $roles[] = new Field(
                            $discordClient,
                            [
                                'name' => $role->id . ') ' . $role->name,
                                'value' => $role->value,
                                'inline' => true,
                            ]
                        );
                    }
                    $discordClient->getChannel($message->channel_id)->sendEmbed(
                        new Embed(
                            $discordClient,
                            [
                                'title' => '__**Points Shop**__',
                                'type' => 'rich',
                                'description' => 'BUY ROLES!',
                                'color' => 0xff0000,
                                'thumbnail' => new Image(
                                    $discordClient,
                                    [
                                        'url' => 'https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/120/emojidex/112/convenience-store_1f3ea.png',
                                    ]
                                ),
                                'fields' => $roles
                            ]
                        )
                    )->done(function(Message $message) {});
                });
                $discordClient->registerCommand('buy', function (Message $message) use ($discordClient) {
                    $command = strtolower(substr($message->content, 5));
                    if ($command === '') {
                        $this->showHelp($discordClient, $message->channel_id);
                    } else {
                        try {
                            $m = Member::find([
                                'id' => $message->author->id,
                                'guild_id' => $message->guild_id,
                            ])->first();
                            if (is_null($m->id)) {
                                throw new Exception('You are not in the points program');
                            }
                            $args = preg_split("/\s/", $command);
                            if (count($args) !== 1) {
                                throw new Exception('Invalid number of parameters.');
                            }
                            $itemNumber = (int) $args[0];
                            $role = Role::where(['id' => $itemNumber])->first();
                            if ($m->balance >= $role->value) {
                                $discordClient->guilds->fetch($m->guild_id)
                                    ->done(function(Guild $guild) use ($m, $role) {
                                        $guild->members->fetch($m->id)
                                            ->done(function(\Discord\Parts\User\Member $member) use ($m, $role) {
                                                $member->addRole($role->roleId);
                                            });
                                    });
                                $m->balance -= $role->value;
                                $m->update();
                                $message->reply("Purchased $role->name for $role->value");
                            } else {
                                throw new Exception('Insufficient points for purchase');
                            }
                        }
                        catch(Exception $exception) {
                            $message->reply($exception->getMessage());
                        }
                    }
                });

                $discordClient->registerCommand('daily', function (Message $message) use ($discordClient) {
                    try {
                        $m = Member::where([
                            'id' => $message->author->id,
                            'guild_id' => $message->guild_id,
                        ])->first();

                        if (is_null($m->id)) {
                            throw new Exception('You are not in the points program');
                        }

                        if (is_null($m->lastDaily)) {
                            $m->lastDaily = new Carbon('last week');
                        }
                        $lastDaily = new Carbon($m->lastDaily);
                        $canReceive = $lastDaily->diffInDays(Carbon::now());

                        $lastTimely = new Carbon($m->lastTimely);
                        $resetTimely = $lastTimely->diffInDays(Carbon::now());
                        if ($resetTimely > 1) {
                            $m->streakTimely = 0;
                        }

                        if ($canReceive > 0) {
                            $prizeAmount = 10 * (1 + $m->streakTimely);
                            $m->balance += $prizeAmount;
                            $m->lastDaily = Carbon::now();
                            $replyText = "You received $prizeAmount points!" . PHP_EOL .
                                "You had a " . (1 + $m->streakTimely) . "x modifier from `.timely`.";
                        } else {
                            $nextDaily = CarbonInterval::make((Carbon::now())->diff($lastDaily->addDay()))->forHumans();
                            $replyText = 'You have already received your daily prize.' . PHP_EOL .
                                'Time until next prize can be claimed: ' . $nextDaily;
                        }
                        $message->reply($replyText);

                        $m->save();
                    }
                    catch(Exception $exception) {
                        $message->reply($exception->getMessage());
                        $this->showHelp($discordClient, $message->channel_id);
                    }
                });

                $discordClient->registerCommand('timely', function (Message $message) use ($discordClient) {
                    try {
                        $m = Member::where([
                            'id' => $message->author->id,
                            'guild_id' => $message->guild_id,
                        ])->first();

                        if (is_null($m->id)) {
                            throw new Exception('You are not in the points program');
                        }

                        if (is_null($m->lastDaily)) {
                            $m->lastTimely = new Carbon('last week');
                        }
                        $lastTimely = new Carbon($m->lastTimely);
                        $canReceive = $lastTimely->diffInDays(Carbon::now());

                        if ($canReceive > 0) {
                            $m->streakTimely += 1;
                            $m->lastTimely = Carbon::now();
                            $replyText = "You now have a " . ($m->streakTimely + 1) . "x streak";
                        } else {
                            $nextTimely = CarbonInterval::make((Carbon::now())->diff($lastTimely->addDay()))->forHumans();
                            $replyText = 'You have already received your timely modifier update.' . PHP_EOL .
                                'Time until you can update again: ' . $nextTimely;
                        }
                        $message->reply($replyText);

                        $m->save();
                    }
                    catch(Exception $exception) {
                        $message->reply($exception->getMessage());
                        $this->showHelp($discordClient, $message->channel_id);
                    }
                });
            } catch (Exception $exception) {
                print_r($exception->getMessage());
            }

            $discordClient->run();
        } catch (Exception $exception) {
            print_r($exception->getMessage() . PHP_EOL);
            echo "Cannot connect to Discord." . PHP_EOL;
        }

        return 0;
    }

    public function showHelp($discordClient, $channelId): void
    {
        $discordClient->getChannel($channelId)->sendEmbed(
            new Embed(
                $discordClient,
                [
                    'type' => 'rich',
                    'color' => 0x00ff00,
                    'title' => '__**Points System**__',
                    'description' => 'Make sure you run `.collect` to be in the system.',
                    'thumbnail' => new Image(
                        $discordClient,
                        [
                            'url' => 'https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/120/facebook/65/money-bag_1f4b0.png'
                        ]
                    ),
                    'fields' => [
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.$ | .bal | .balance`',
                                'value' => 'shows current point balance',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.send # @user`',
                                'value' => 'sends @user `#` number of points',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.shop`',
                                'value' => 'see what is available at the shop',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.buy #`',
                                'value' => 'buy item from shop',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.daily`',
                                'value' => 'get a daily bonus of 100 pts',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.timely`',
                                'value' => 'try to get a 7 day streak bonus',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.flip #`',
                                'value' => 'Place a bet of `#`, flip a coin, see if you win 1.5x',
                                'inline' => false
                            ]
                        ),
                        new Field(
                            $discordClient,
                            [
                                'name'  => '`.wheel #`',
                                'value' => 'Place a bet of `#`, spin the wheel, win a prize',
                                'inline' => false
                            ]
                        ),
                    ]
                ]
            )
        );
    }
}
