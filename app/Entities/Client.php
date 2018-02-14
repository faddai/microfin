<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;


class Client extends Model
{
    use Notifiable;

    protected $fillable = [
        'phone1', 'phone2', 'status', 'relationship_manager', 'email', 'address', 'clientable_id', 'clientable_type',
        'nationality', 'account_number', 'branch_id', 'created_by', 'identification_type', 'identification_number',
        'name', 'photo', 'signature', 'account_balance'
    ];

    /**
     * The user who created/opened the account for this client
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relationshipManager()
    {
        return $this->belongsTo(User::class, 'relationship_manager');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function clientable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'nationality', 'alpha_2_code');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guarantors()
    {
        return $this->hasMany(Guarantor::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(ClientTransaction::class);
    }

    public function getProfilePhoto()
    {
        return $this->photo ? Storage::url($this->photo) : asset('img/default-user.png');
    }

    /**
     * @return mixed|string
     */
    public function getLastFourDigitsOfAccountNumber() {
        if (strlen($this->account_number) > 4) {
            return str_repeat('*', strlen($this->account_number) - 4) . substr($this->account_number, -4);
        }
        return $this->account_number;
    }

    public function isIndividual()
    {
        return $this->clientable instanceof IndividualClient;
    }

    /**
     * @return bool
     */
    public function isCorporate()
    {
        return $this->clientable instanceof CorporateClient;
    }

    public static function scopeSearch($query, $searchTerm)
    {
        return $query->where('account_number', 'LIKE', "%$searchTerm%")
            ->orWhere('name', 'LIKE', "%$searchTerm%")
            ->take(3)
            ->get();
    }

    public function getDisplayName()
    {
        return sprintf('%s (%s)', $this->getLastFourDigitsOfAccountNumber(), $this->getFullName() ?: '');
    }

    public function getNameAttribute()
    {
        if ($this->clientable instanceof IndividualClient) {
            $name = $this->clientable->firstname .' ';

            if ($middlename = $this->clientable->middlename) {
                $name .= $middlename. ' ';
            }

            $name .= $this->clientable->lastname;

            return $name;
        }

        return $this->clientable->company_name;
    }

    public function getFullName($uppercased = true)
    {
        return $uppercased ? strtoupper($this->name) : $this->name;
    }

    /**
     * Checks if Client has any more funds that can be deducted to repay
     * the scheduled repayment amount
     *
     * @param int $amount
     * @return bool
     */
    public function isDeductable($amount = 0)
    {
        if ($this->getAccountBalance(false) <= 0.0) {
            return false;
        }

        return $this->account_balance >= $amount;
    }

    /**
     * @param bool $format
     * @return mixed|string
     */
    public function getAccountBalance($format = true)
    {
        $balance = $this->fresh()->account_balance;

        return $format ? number_format($balance, 2) : round($balance, 2);
    }

    /**
     * Determines whether a Client can receive an email notifications
     * @return bool
     */
    public function canReceiveEmailNotification()
    {
        return $this->email !== null && trim($this->email) !== '';
    }

    /**
     * Determines whether a Client can receive SMS notifications
     * @return bool
     */
    public function canReceiveSmsNotification()
    {
        return ! $this->phone1 !== null || ! $this->phone2 !== null;
    }
}
