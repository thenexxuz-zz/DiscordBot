<?php

namespace Database\Seeders;

use App\Models\Guild;
use App\Models\Role;
use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        try {
            $discordClient = new Discord([
                'token' => env('DISCORD_TOKEN'),
            ]);
            $discordClient->on('ready', function ($discordClient) {
                $guild = Guild::find(env('DISCORD_GUILD_ID'));
                $roles = [
                    ['name' => 'Purple', 'value' => 1000, "color" => 0xff00ff],
                    ['name' => 'Blue',   'value' => 100,  "color" => 0x0000ff],
                    ['name' => 'Green',  'value' => 80,   "color" => 0x00ff00],
                    ['name' => 'Yellow', 'value' => 60,   "color" => 0xffff00],
                    ['name' => 'Orange', 'value' => 40,   "color" => 0xffa500],
                    ['name' => 'Red',    'value' => 20,   "color" => 0xff0000],
                ];
                foreach ($roles as $role) {
                    $r = new Role([
                        'name'      => $role['name'],
                        'value'     => $role['value'],
                        'available' => true,
                        'guildId' => $guild->id,
                    ]);
                    $r->save();
                    $discordClient->guilds->fetch($guild->id)
                        ->done(function (DiscordGuild $guild) use ($discordClient, $roles, $role, $r) {
                            $guild->createRole([
                                'name'        => $role['name'],
                                'mentionable' => false,
                                'hoist'       => false,
                                'color'       => $role['color'],
                                'guild'       => $guild,
                                'guild_id'    => $guild->id,
                            ])->done(function (DiscordRole $dr) use ($discordClient, $roles, $r) {
                                $updateRole = Role::where('id', $r->id)->first();
                                $updateRole->roleId = $dr->id;
                                $updateRole->save();
                                if (count($roles) === (int) $r->id) {
                                    $discordClient->close();
                                }
                            });
                        });
                }
            });
            $discordClient->run();
        }
        catch (IntentException $e) {
            \Log::info($e->getMessage());
        }
    }
}
