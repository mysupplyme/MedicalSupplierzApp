<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'date',
        'time',
        'duration',
        'price',
        'image',
        'speaker',
        'capacity',
        'registered',
        'tags',
        'status',
        'category_id',
        'specialty_id',
        'sub_specialty_id',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'tags' => 'array',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function subSpecialty()
    {
        return $this->belongsTo(SubSpecialty::class);
    }

    public function isFull()
    {
        return $this->registered >= $this->capacity;
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canRegister()
    {
        return !$this->isFull() && !$this->isCompleted();
    }
}