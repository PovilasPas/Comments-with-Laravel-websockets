<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'evaluations';
    
    protected $primaryKey = ['user_id', 'comment_id'];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('user_id', $this->getAttribute('user_id'))
            ->where('comment_id', $this->getAttribute('comment_id'));
    }

    protected $fillable = [
        'user_id',
        'comment_id',
        'votedUp'
    ];

    public $timestamps = false;
}
