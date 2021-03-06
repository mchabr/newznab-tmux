<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseFile extends Model
{
    /**
     * @var bool
     */
    protected $dateFormat = false;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $primaryKey = 'releases_id';

    public function release()
    {
        return $this->belongsTo(Release::class, 'releases_id');
    }
}
