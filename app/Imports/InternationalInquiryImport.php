<?php

namespace App\Imports;

use App\Models\InternationInquiry;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use App\Models\BlockedInternationalInquiry;
use App\Models\BlockedInternationalOffer;
use App\Models\BlockedInternationalOrder;
use App\Rules\UniqueMobileAcrossTables;
use App\Models\InternationalOffer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;




class InternationalInquiryImport implements ToCollection, WithHeadingRow
{
    public $errors = [];
    protected $imported = [];

    public function collection(Collection $rows)
    {
        $validRows = [];
        $rowNumber = 1;
        
        $nextInquiryNumber = InternationInquiry::max('inquiry_number') ?? 0;
        $nextInquiryNumber++;

        $nextOfferNumber = InternationalOffer::max('offer_number') ?? 0;
        $nextOfferNumber++;

        foreach ($rows as $row) {

            if (
                BlockedInternationalInquiry::where('mobile_number', $row['mobile_number'])->exists() ||
                BlockedInternationalOffer::where('mobile_number', $row['mobile_number'])->exists() ||
                BlockedInternationalOrder::where('mobile_number', $row['mobile_number'])->exists()
            ) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['This international inquiry is blocked.']
                ];
                $rowNumber++;
                continue;
            }

             $validator = Validator::make($row->toArray(), [
                // 'mobile_number'      => 'required|digits:10',
                'mobile_number'      => 'required',
                'inquiry_date'       => 'required',
                'product_categories' => 'nullable|string',
                'specific_product'   => 'nullable|string',
                'name'               => 'nullable|string|max:255',
                'location'           => 'nullable|string|max:255',
                'inquiry_through'    => 'nullable|string|max:255',
                'inquiry_reference'  => 'nullable|string|max:255',
                'first_contact_date' => 'nullable',
                'first_response'     => 'nullable|string|max:255',
                'second_contact_date'=> 'nullable',
                'second_response'     => 'nullable|string|max:255',
                'third_contact_date'=> 'nullable',
                'third_response'     => 'nullable|string|max:255',
                'notes'     => 'nullable|string|max:255',
                'status'      => 'required',
            ]);
            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => $validator->errors()->all()
                ];
                $rowNumber++;
                continue;
            }

            $inquiryDate = $this->parseDate($row['inquiry_date']);
            $firstContact = $this->parseDate($row['first_contact_date']);
            $secondContact = $this->parseDate($row['second_contact_date']);
            $thirdContact = $this->parseDate($row['third_contact_date']);


            $validRows[] = [
            'international_inquiry' => [
                'inquiry_number'        => $nextInquiryNumber++,
                'mobile_number'         => $row['mobile_number'],
                'inquiry_date'          => $inquiryDate,
                'product_categories'    => $row['product_categories'],
                'specific_product'      => $row['specific_product'],
                'name'                  => $row['name'],
                'location'              => $row['location'],
                'inquiry_through'       => $row['inquiry_through'],
                'inquiry_reference'     => $row['inquiry_reference'],
                'first_contact_date'    => $firstContact,
                'first_response'        => $row['first_response'],
                'second_contact_date'   => $secondContact,
                'second_response'       => $row['second_response'] ?? null,
                'third_contact_date'    => $thirdContact,
                'third_response'        => $row['third_response'] ?? null,
                'notes'                 => $row['notes'] ?? null,
                'status'                => $row['status'],
                'user_id'               => auth()->id(),
            ],
                'should_create_offer' => (int) $row['status'] === 1,
            ];
                $rowNumber++;

        }

        if (!empty($this->errors)) {
            return;
        }

        foreach ($validRows as $row) {
            $international_inquiry = InternationInquiry::create($row['international_inquiry']);

            if ($row['should_create_offer']) {
                InternationalOffer::create([
                    'international_inquiry_id'    => $international_inquiry->id,
                    'offer_number' => $nextOfferNumber++,
                ]);
            }
            $this->imported[] = $international_inquiry;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getImported()
    {
        return $this->imported;
    }

    private function parseDate($date)
    {
        if (!$date) return null;

        try {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            \Log::error("Date parse error: " . $e->getMessage());
            return null;
        }
    }
}

