<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;


class Deposit extends Model
{
    protected $fillable = ['amount', 'client_id', 'user_id', 'narration', 'account_id', 'receipt'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
