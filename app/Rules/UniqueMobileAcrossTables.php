<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            if (request()->input('force')) {
                return true;
            }


            // Check special cases where existence means OK
            $existsAsOrderFlow = DB::table('inquiries')
                ->where('mobile_number', $value)
                ->where('status', 1)
                ->where('offers_status', 1)
                ->exists();

            if ($existsAsOrderFlow) {
                return true;
            }

            $existsIntlAsOrderFlow = DB::table('international_inquiries')
                ->where('mobile_number', $value)
                ->where('status', 1)
                ->where('offers_status', 1)
                ->exists();

            if ($existsIntlAsOrderFlow) {
                return true;
            }

            if ($this->excludeId) {
                $tablesToCheck = [
                    'inquiries',
                    'international_inquiries',
                    'orders',
                    'international_orders',
                ];

                foreach ($tablesToCheck as $table) {
                    $original = DB::table($table)->where('id', $this->excludeId)->value('mobile_number');
                    if ($original === $value) {
                        return true;
                    }
                }
            }



            // Check blocked tables
            $isBlocked = DB::table('blocked_inquiries')->where('mobile_number', $value)->exists()
                || DB::table('blocked_domestic_offers')->where('mobile_number', $value)->exists()
                || DB::table('blocked_orders')->where('mobile_number', $value)->exists()
                || DB::table('blocked_international_inquiries')->where('mobile_number', $value)->exists()
                || DB::table('blocked_international_offers')->where('mobile_number', $value)->exists()
                || DB::table('blocked_international_orders')->where('mobile_number', $value)->exists();


            $conflicts = [];


            if ($isBlocked) {
                $conflicts[] = 'blocked';
            }

            // Domestic Inquiries
                $inquiryRecords = DB::table('inquiries')
                    ->where('mobile_number', $value)
                    ->get();

                if ($inquiryRecords->count()) {
                    foreach ($inquiryRecords as $record) {
                        $conflicts = array_merge($conflicts, $this->detectConflictType($record));
                    }
                }

                // Domestic Orders

                $orderRecords = DB::table('orders')
                    ->where('mobile_number', $value)
                    ->get();

                if ($orderRecords->count()) {
                    foreach ($orderRecords as $record) {
                        $conflicts = array_merge($conflicts, $this->detectConflictType($record));
                    }
                }


                // International Inquiries
                $intlInquiryRecords = DB::table('international_inquiries')
                    ->where('mobile_number', $value)
                    ->get();

                if ($intlInquiryRecords->count()) {
                    foreach ($intlInquiryRecords as $record) {
                        $types = $this->detectConflictType($record);
                        foreach ($types as $type) {
                            $conflicts[] = 'international_' . $type;
                        }
                    }
                }

                // International Orders
                $intlOrderRecords = DB::table('international_orders')
                    ->where('mobile_number', $value)
                    ->get();

                if ($intlOrderRecords->count()) {
                    foreach ($intlOrderRecords as $record) {
                        $types = $this->detectConflictType($record);
                        foreach ($types as $type) {
                            $conflicts[] = 'international_' . $type;
                        }
                    }
                }

                if (!empty($conflicts)) {
                    $this->conflictSource = implode(', ', array_unique($conflicts));
                    return false;
                }


            // No conflicts found
            return true;

    }

    protected function detectConflictType(object $record): array
    {
        $conflicts = [];

        // Use null safe checks for properties in case they don't exist
        $status = $record->status ?? null;
        $offersStatus = $record->offers_status ?? null;
        $ordersStatus = $record->orders_status ?? null;

        if ($status === 2) {
            $conflicts[] = 'inquiries';
        } elseif ($status === 1) {
            $conflicts[] = 'offers';
        } elseif ($status === 0) {
            $conflicts[] = 'cancellations';
        }

        if ($offersStatus === 0) {
            $conflicts[] = 'offer_cancellations';
        } elseif ($offersStatus === 1) {
            $conflicts[] = 'orders';
        }

        if ($ordersStatus === 0) {
            $conflicts[] = 'order_cancellations';
        }

        return $conflicts;
    }

    public function message(): string
    {
        // Map conflict source to user-friendly messages
        $map = [
            'blocked' => 'blocked',
            'inquiries' => 'Domestic Inquiries',
            'offers' => 'Domestic Offers',
            'cancellations' => 'Domestic Inquiry Cancellations',
            'offer_cancellations' => 'Domestic Offer Cancellations',
            'orders' => 'Domestic Orders',
            'order_cancellations' => 'Domestic Order Cancellations',
            'international_inquiries' => 'International Inquiries',
            'international_offers' => 'International Offers',
            'international_cancellations' => 'International Inquiry Cancellations',
            'international_offer_cancellations' => 'International Offer Cancellations',
            'international_orders' => 'International Orders',
            'international_order_cancellations' => 'International Order Cancellations',
        ];

        // If conflictSource has multiple values separated by commas, map each and join
        if ($this->conflictSource) {
            $sources = array_map('trim', explode(',', $this->conflictSource));
            $messages = [];

            foreach ($sources as $source) {
                $messages[] = '<li> - ' . ($map[$source]) . '</li>';

            }
           return '<ul>' . implode('', $messages) . '</ul>';
        }

        return '<ul><li>This mobile number already exists.</li></ul>';

    }
}

