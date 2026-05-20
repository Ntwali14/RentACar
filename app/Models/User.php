<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\UserRole;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the messages for the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the inspections performed by the user.
     */
    public function performedInspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class, 'inspected_by');
    }

    /**
     * Get the evidence uploaded by the user.
     */
    public function uploadedEvidence(): HasMany
    {
        return $this->hasMany(InspectionEvidence::class, 'uploaded_by');
    }

    /**
     * Get the damage reports created by the user.
     */
    public function reportedDamages(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'reported_by');
    }

    /**
     * Get the disputes where user is the customer.
     */
    public function customerDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'customer_id');
    }

    /**
     * Get the disputes where user is the admin.
     */
    public function adminDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'admin_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    public function canAccessAdmin(): bool
    {
        return $this->isAdmin();
    }
}
