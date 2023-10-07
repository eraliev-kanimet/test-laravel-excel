<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItemName extends Model
{
    protected $fillable = [
        'budget_item_category_id',
        'name',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BudgetItemCategory::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
