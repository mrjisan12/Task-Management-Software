<?php

namespace App\Support;

use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdminCompanyScope
{
    public static function user(): ?User
    {
        return Auth::user();
    }

    public static function isPlatformAdmin(?User $user = null): bool
    {
        $user ??= static::user();

        return (bool) $user?->hasAnyRole(['super_admin', 'platform_admin']);
    }

    public static function isCompanyAdmin(?User $user = null): bool
    {
        $user ??= static::user();

        return (bool) $user?->hasRole('company_admin');
    }

    public static function companyId(?User $user = null): ?int
    {
        $user ??= static::user();

        if (! $user) {
            return null;
        }

        return app(CompanyContext::class)->current($user)?->id;
    }

    public static function companyQuery(Builder $query, string $column = 'company_id'): Builder
    {
        if (static::isPlatformAdmin()) {
            return $query;
        }

        return $query->where($column, static::companyId() ?: 0);
    }

    public static function companyOrGlobalQuery(Builder $query, string $column = 'company_id'): Builder
    {
        if (static::isPlatformAdmin()) {
            return $query;
        }

        return $query->where($column, static::companyId() ?: 0);
    }

    public static function userQuery(Builder $query): Builder
    {
        if (static::isPlatformAdmin()) {
            return $query;
        }

        return $query->whereHas('companyMemberships', fn (Builder $membershipQuery) => $membershipQuery
            ->where('company_id', static::companyId() ?: 0)
            ->where('status', 'active'));
    }

    public static function companySelect(Select $select, bool $required = true): Select
    {
        $select = $select
            ->relationship('company', 'name')
            ->searchable()
            ->preload()
            ->default(fn () => static::isPlatformAdmin() ? null : static::companyId())
            ->disabled(fn () => ! static::isPlatformAdmin())
            ->dehydrated();

        return $required ? $select->required() : $select;
    }
}
