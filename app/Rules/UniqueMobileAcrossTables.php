<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueMobileAcrossTables implements Rule
{
    protected ?string $conflictSource = null;

    public function passes($attribute, $value): bool
    {
        if (DB::table('inquiries')->where('mobile_number', $value)->exists()) {
            $this->conflictSource = 'inquiries';
            return false;
        }

        if (DB::table('orders')->where('mobile_number', $value)->exists()) {
            $this->conflictSource = 'orders';
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return match ($this->conflictSource) {
            'inquiries' => 'Mobile number already exists in inquiries.',
            'orders' => 'Mobile number already exists in orders.',
            default => 'This mobile number already exists.',
        };
    }
}
