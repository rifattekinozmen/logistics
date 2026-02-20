<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KvkkComplianceService
{
    /**
     * Export all personal data for a user (KVKK/GDPR Right to Access).
     *
     * @param  User  $user  User to export data for
     * @return string Path to exported ZIP file
     */
    public function exportPersonalData(User $user): string
    {
        $exportData = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'employee' => $this->getEmployeeData($user),
            'orders' => $this->getOrderData($user),
            'activity_log' => $this->getActivityLog($user),
        ];

        $filename = 'personal_data_export_'.$user->id.'_'.now()->format('Y-m-d_His').'.json';
        $path = 'exports/'.$filename;

        Storage::put($path, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    /**
     * Anonymize user data (KVKK/GDPR Right to be Forgotten).
     *
     * @param  User  $user  User to anonymize
     * @return bool Success status
     */
    public function anonymizeUser(User $user): bool
    {
        DB::beginTransaction();

        try {
            // Anonymize user record
            $user->update([
                'name' => 'Anonymized User #'.$user->id,
                'email' => 'anonymized_'.$user->id.'@example.com',
                'phone' => null,
            ]);

            // Anonymize employee record if exists
            if ($employee = $user->employee) {
                $employee->update([
                    'name' => 'Anonymized Employee',
                    'phone' => null,
                    'email' => null,
                    'address' => null,
                    'identity_number' => null,
                ]);
            }

            // Keep order records but remove personal notes
            DB::table('orders')
                ->where('created_by', $user->id)
                ->update(['notes' => null]);

            // Log anonymization
            activity()
                ->causedBy($user)
                ->log('User data anonymized per KVKK/GDPR request');

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    /**
     * Encrypt sensitive personal data field.
     *
     * @param  string  $data  Data to encrypt
     * @return string Encrypted data
     */
    public function encryptPersonalData(string $data): string
    {
        return Crypt::encryptString($data);
    }

    /**
     * Decrypt sensitive personal data field.
     *
     * @param  string  $encryptedData  Encrypted data
     * @return string Decrypted data
     */
    public function decryptPersonalData(string $encryptedData): string
    {
        return Crypt::decryptString($encryptedData);
    }

    /**
     * Get consent status for a user.
     *
     * @param  User  $user  User to check
     * @return array<string, mixed> Consent status
     */
    public function getConsentStatus(User $user): array
    {
        // This would typically fetch from a consents table
        return [
            'data_processing' => true,
            'marketing_communications' => false,
            'third_party_sharing' => false,
            'last_updated' => now()->toIso8601String(),
        ];
    }

    /**
     * Record user consent.
     *
     * @param  User  $user  User giving consent
     * @param  string  $consentType  Type of consent
     * @param  bool  $granted  Whether consent is granted
     */
    public function recordConsent(User $user, string $consentType, bool $granted): void
    {
        // Log consent in activity log
        activity()
            ->causedBy($user)
            ->withProperties([
                'consent_type' => $consentType,
                'granted' => $granted,
                'ip_address' => request()->ip(),
            ])
            ->log('User consent recorded: '.$consentType);
    }

    /**
     * Get employee data for user.
     *
     * @param  User  $user  User
     * @return array<string, mixed>|null Employee data
     */
    protected function getEmployeeData(User $user): ?array
    {
        $employee = $user->employee;

        if (! $employee) {
            return null;
        }

        return [
            'name' => $employee->name,
            'position' => $employee->position?->name,
            'branch' => $employee->branch?->name,
            'hire_date' => $employee->created_at->toIso8601String(),
        ];
    }

    /**
     * Get order data for user.
     *
     * @param  User  $user  User
     * @return array<int, array> Orders data
     */
    protected function getOrderData(User $user): array
    {
        return DB::table('orders')
            ->where('created_by', $user->id)
            ->select('order_number', 'status', 'created_at')
            ->get()
            ->map(fn ($order) => (array) $order)
            ->toArray();
    }

    /**
     * Get activity log for user.
     *
     * @param  User  $user  User
     * @return array<int, array> Activity log
     */
    protected function getActivityLog(User $user): array
    {
        return DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->select('description', 'created_at')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($log) => (array) $log)
            ->toArray();
    }
}
