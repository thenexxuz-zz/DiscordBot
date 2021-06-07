<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Member
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $nick
 * @property int $guild_id
 * @property int $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Member newModelQuery()
 * @method static Builder|Member newQuery()
 * @method static Builder|Member query()
 * @method static Builder|Member whereBalance($value)
 * @method static Builder|Member whereCreatedAt($value)
 * @method static Builder|Member whereGuildId($value)
 * @method static Builder|Member whereId($value)
 * @method static Builder|Member whereNick($value)
 * @method static Builder|Member whereUpdatedAt($value)
 * @method static Builder|Member whereUsername($value)
 * @mixin Eloquent
 */
class Member extends Model
{
    protected $fillable = ['id', 'guild_id'];
}
