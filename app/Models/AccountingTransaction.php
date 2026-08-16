<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingTransaction extends Model
{
    protected $fillable = [
        'reference_number', 'type', 'occurred_at', 'source_type', 'customer_id', 'company_id',
        'customer_name', 'customer_email', 'customer_address', 'issuer_name', 'issuer_email',
        'issuer_address', 'subtotal', 'tax_total', 'total', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccountingTransactionItem::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AccountingTransactionAttachment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when(in_array($filters['type'] ?? null, ['income', 'expense'], true),
                fn (Builder $q) => $q->where('type', $filters['type']))
            ->when(in_array($filters['source'] ?? null, ['company', 'personal'], true),
                fn (Builder $q) => $q->where('source_type', $filters['source']))
            ->when(filled($filters['company_id'] ?? null),
                fn (Builder $q) => $q->where('company_id', $filters['company_id']))
            ->when(filled($filters['from'] ?? null), function (Builder $q) use ($filters) {
                $q->whereDate('occurred_at', '>=', $filters['from']);

                if (filled($filters['to'] ?? null)) {
                    $q->whereDate('occurred_at', '<=', $filters['to']);
                }
            });
    }
}
