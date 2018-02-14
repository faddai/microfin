<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 08/04/2017
 * Time: 04:58
 */

namespace App\Entities;


use App\Scopes\OrderByCreatedAtScope;
use Illuminate\Database\Eloquent\Model;

class LoanStatementEntry extends Model
{
    protected $fillable = ['cr', 'dr', 'value_date', 'narration', 'loan_statement_id', 'created_at'];

    protected $dates = ['value_date'];

    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope(new OrderByCreatedAtScope);
    }

    public function statement()
    {
        return $this->belongsTo(LoanStatement::class, 'loan_statement_id');
    }

    public function getDebitAmount($format = true)
    {
        return $format ? number_format($this->dr, 2) : $this->dr;
    }

    public function getCreditAmount($format = true)
    {
        return $format ? number_format($this->cr, 2) : $this->cr;
    }

    public function getBalance($format = true)
    {
        return $format ? number_format($this->balance, 2) : $this->balance;
    }

}