<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiReport extends Model
{
    use HasFactory;

    protected $table = 'ai_reports';

    protected $fillable = [
        'type',
        'summary_text',
        'severity',
        'data_snapshot',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'data_snapshot' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
