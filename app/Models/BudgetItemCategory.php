<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItemCategory extends Model
{
    protected $fillable = [
        'budget_id',
        'name',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function itemsName(): HasMany
    {
        return $this->hasMany(BudgetItemName::class);
    }
}
