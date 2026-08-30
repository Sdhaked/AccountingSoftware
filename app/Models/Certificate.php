<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    protected $fillable = [
        'certificate_number',
        'customer_id',
        'company_id',
        'course_name',
        'instructor_name',
        'instructor_signature_path',
        'issued_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function instructorSignatureDataUri(): ?string
    {
        if (! $this->instructor_signature_path || ! Storage::disk('public')->exists($this->instructor_signature_path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($this->instructor_signature_path) ?: 'image/png';

        return 'data:'.$mime.';base64,'
            .base64_encode(Storage::disk('public')->get($this->instructor_signature_path));
    }
}
