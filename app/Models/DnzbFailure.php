<?php

namespace App\Models;

use Yadakhov\InsertOnDuplicateKey;
use Illuminate\Database\Eloquent\Model;

class DnzbFailure extends Model
{
    use InsertOnDuplicateKey;
    /**
     * @var string
     */
    protected $table = 'dnzb_failures';

    /**
     * @var bool
     */
    protected $dateFormat = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $guarded = [];

    public function release()
    {
        return $this->belongsTo(Release::class, 'release_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
