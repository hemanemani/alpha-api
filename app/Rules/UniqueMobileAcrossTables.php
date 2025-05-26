<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueMobileAcrossTables implements Rule
{
    protected ?string $conflictSource = null;
    protected ?int $excludeId;

    public function __construct(?int $excludeId = null)
    {
        $this->excludeId = $excludeId;
    }



    public function passes($attribute, $value): bool
    {

        $isBlocked = DB::table('blocked_inquiries')->where('mobile_number', $value)->exists() || DB::table('blocked_domestic_offers')->where('mobile_number', $value)->exists() || DB::table('blocked_orders')->where('mobile_number', $value)->exists() || DB::table('blocked_international_inquiries')->where('mobile_number', $value)->exists() || DB::table('blocked_international_offers')->where('mobile_number', $value)->exists() || DB::table('blocked_international_orders')->where('mobile_number', $value)->exists();

        if ($isBlocked) {
            $this->conflictSource = 'blocked';
            return false;
        }

        $tables = [
            'inquiries' => true,
            'orders' => true,
            'international_inquiries' => true,
            'international_orders' => true,
        ];

        foreach ($tables as $table => $shouldExcludeId) {
            $query = DB::table($table)->where('mobile_number', $value);

            if ($shouldExcludeId && $this->excludeId) {
                $query->where('id', '!=', $this->excludeId);
            }

            if ($query->exists()) {
                $this->conflictSource = $table;
                return false;
            }
        }
        return true;

    }

    public function message(): string
    {
        return match ($this->conflictSource) {
            'blocked' => 'This inquiry is blocked.',
            'inquiries' => 'Mobile number already exists in inquiries.',
            'orders' => 'Mobile number already exists in orders.',
            'international_inquiries' => 'Mobile number already exists in international inquiries.',
            'international_orders' => 'Mobile number already exists in international orders.',
            default => 'This mobile number already exists.',

        };
    }
}
