<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Frequency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringInvoiceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'client_id',
        'frequency',
        'next_issue_date',
        'end_date',
        'items',
        'notes',
        'tax_rate',
        'is_active',
        'last_generated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'frequency' => Frequency::class,
            'next_issue_date' => 'date',
            'end_date' => 'date',
            'items' => 'array',
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'last_generated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @param  Builder<RecurringInvoice>  $query
     * @return Builder<RecurringInvoice>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<RecurringInvoice>  $query
     * @return Builder<RecurringInvoice>
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->active()->where('next_issue_date', '<=', now());
    }

    /**
     * @param  Builder<RecurringInvoice>  $query
     * @return Builder<RecurringInvoice>
     */
    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }
}
