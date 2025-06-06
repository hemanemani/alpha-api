<?php

namespace App\Imports;

use App\Models\Inquiry;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use App\Rules\UniqueMobileAcrossTables;
use App\Models\BlockedInquiry;
use App\Models\BlockedOffer;
use App\Models\BlockedOrder;
use App\Models\Offer;


class InquiryImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    public $errors = [];
    protected $currentRow = 1;

    public function model(array $row)
    {
        $this->currentRow++;
        static $nextInquiryNumber = null;
        $offer_number = null;


        if (is_null($nextInquiryNumber)) {
            $nextInquiryNumber = Inquiry::max('inquiry_number') ?? 0;
            $nextInquiryNumber++;
        }




        // Blocked mobile number check
        if (
            BlockedInquiry::where('mobile_number', $row['mobile_number'])->exists() ||
            BlockedOffer::where('mobile_number', $row['mobile_number'])->exists() ||
            BlockedOrder::where('mobile_number', $row['mobile_number'])->exists()
        ) {
            $this->errors[] = [
                'row'    => $this->currentRow,
                'errors' => ['This inquiry is blocked.']
            ];
            return null;
        }
    

        $validator = Validator::make($row, [
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
                'row'    => $this->currentRow,
                'errors' => $validator->errors()->all()
            ];
            return null;
        }

        try {
            // Make sure to log and check the date parsing
            $inquiry_date = Carbon::createFromFormat('d-m-Y', $row['inquiry_date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $inquiry_date = null;
            \Log::error("Error parsing inquiry_date: " . $e->getMessage());
        }
    
        try {
            $first_contact_date = Carbon::createFromFormat('d-m-Y', $row['first_contact_date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $first_contact_date = null;
            \Log::error("Error parsing first_contact_date: " . $e->getMessage());
        }
    
    
        $second_contact_date = !empty($row['second_contact_date']) 
            ? $this->parseDate($row['second_contact_date']) 
            : null;
    
        $third_contact_date = !empty($row['third_contact_date']) 
            ? $this->parseDate($row['third_contact_date']) 
            : null;


        if ((int) $row['status'] === 1) {
            $lastOfferNumber = Offer::max('offer_number') ?? 0;
            $offer_number = $lastOfferNumber + 1;
        }


        $inquiry =  new Inquiry([
            'inquiry_number'        => $nextInquiryNumber++,
            'mobile_number'         => $row['mobile_number'],
            'inquiry_date'          => $inquiry_date,
            'product_categories'    => $row['product_categories'],
            'specific_product'      => $row['specific_product'],
            'name'                  => $row['name'],
            'location'              => $row['location'],
            'inquiry_through'       => $row['inquiry_through'],
            'inquiry_reference'     => $row['inquiry_reference'],
            'first_contact_date'    => $first_contact_date,
            'first_response'        => $row['first_response'],
            'second_contact_date'   => $second_contact_date,
            'second_response'       => $row['second_response'] ?? null,
            'third_contact_date'    => $third_contact_date,
            'third_response'        => $row['third_response'] ?? null,
            'notes'                 => $row['notes'] ?? null,
            'status'                => $row['status'],
            'user_id'               => auth()->id(),
        ]);

        $inquiry->save();

        if ((int) $row['status'] === 1) {
            $lastOfferNumber = \App\Models\Offer::max('offer_number') ?? 0;
            $newOfferNumber = $lastOfferNumber + 1;

            \App\Models\Offer::create([
                'inquiry_id'    => $inquiry->id,
                'offer_number'  => $newOfferNumber,
            ]);
        }

        return $inquiry;


    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = [
                'row'    => $failure->row(), // Row number in Excel
                'errors' => $failure->errors() // List of validation errors
            ];
        }
    }


    public function getErrors()
    {
        return $this->errors;
    }
    
    private function parseDate($date)
    {
        try {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

}

