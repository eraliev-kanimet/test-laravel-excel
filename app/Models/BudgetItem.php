<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_item_name_id',
        'month',
        'amount',
        'description',
    ];

    public function name(): BelongsTo
    {
        return $this->belongsTo(BudgetItemName::class);
    }
}
