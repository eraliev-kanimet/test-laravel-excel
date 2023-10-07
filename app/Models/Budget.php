<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'name',
    ];

    public function itemsCategory(): HasMany
    {
        return $this->hasMany(BudgetItemCategory::class);
    }
}
