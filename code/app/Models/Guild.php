<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Guild
 *
 * @property int $id
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Guild newModelQuery()
 * @method static Builder|Guild newQuery()
 * @method static Builder|Guild query()
 * @method static Builder|Guild whereCreatedAt($value)
 * @method static Builder|Guild whereId($value)
 * @method static Builder|Guild whereName($value)
 * @method static Builder|Guild whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Guild extends Model
{
    protected $fillable = ['id'];
}
