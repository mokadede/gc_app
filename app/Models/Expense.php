<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expense_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function categoryLabels(): array
    {
        return [
            'deterjen' => 'Deterjen / Sabun',
            'pewangi' => 'Pewangi / Softener',
            'plastik' => 'Plastik / Packaging',
            'listrik_air' => 'Listrik / Air',
            'gaji' => 'Gaji Karyawan',
            'sewa' => 'Sewa Tempat',
            'transportasi' => 'Transportasi / Bensin',
            'lainnya' => 'Lain-lain',
        ];
    }
}
