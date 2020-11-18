<?php

namespace App\Console\Commands;

use Discord\DiscordCommandClient;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event;
use Illuminate\Console\Command;

class MemberCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:member-collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            ]);
        }
        catch (\Exception $exception) {
            echo "Cannot connect to Discord.".PHP_EOL;
        }

        if (!is_null($discordClient)) {
            $discordClient->on('ready', function($discord) {
                $discord->on(Event::GUILD_MEMBER_ADD, function(Member $member) {
                    $g = \App\Models\Guild::firstOrCreate([
                        'id' => $member->guild->id,
                    ]);
                    $g->name = $member->guild->name;
                    $g->save();

                    $m = \App\Models\Member::firstOrCreate([
                        'id' => $member->id,
                        'guild_id' => $member->guild->id,
                    ]);
                    $m->username = $member->username;
                    $m->nick = $member->nick;
                    $m->save();
                });
            });

            $discordClient->run();
        }
        return 0;
    }
}
