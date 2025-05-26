<?php

namespace App\Imports;

use App\Models\InternationInquiry;
use Maatwebsite\Excel\Concerns\ToModel;
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



class InternationalInquiryImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    public $errors = [];
    protected $currentRow = 1;

    public function model(array $row)
    {
        $this->currentRow++;
        static $nextInquiryNumber = null;

        if (is_null($nextInquiryNumber)) {
            $nextInquiryNumber = InternationInquiry::max('inquiry_number') ?? 0;
            $nextInquiryNumber++;
        }
        
        // Blocked mobile number check
        if (
            BlockedInternationalInquiry::where('mobile_number', $row['mobile_number'])->exists() ||
            BlockedInternationalOffer::where('mobile_number', $row['mobile_number'])->exists() ||
            BlockedInternationalOrder::where('mobile_number', $row['mobile_number'])->exists()
        ) {
            $this->errors[] = [
                'row'    => $this->currentRow,
                'errors' => ['This inquiry is blocked.']
            ];
            return null;
        }

        // UniqueMobileAcrossTables validation (manual)
        $rule = new UniqueMobileAcrossTables;
        if (!$rule->passes('mobile_number', $row['mobile_number'])) {
            $this->errors[] = [
                'row'    => $this->currentRow,
                'errors' => [$rule->message()]
            ];
            return null;
        }

        $validator = Validator::make($row, [
            'mobile_number'      => 'required',
            'inquiry_date'       => 'required|date_format:d-m-Y',
            'product_categories' => 'required|string',
            'specific_product'   => 'required|string',
            'name'               => 'required|string|max:255',
            'location'           => 'nullable|string|max:255',
            'inquiry_through'    => 'nullable|string|max:255',
            'inquiry_reference'  => 'nullable|string|max:255',
            'first_contact_date' => 'nullable|date_format:d-m-Y',
            'first_response'     => 'nullable|string|max:255',
            'second_contact_date'=> 'nullable|date_format:d-m-Y',
            
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
    
    
        return new InternationInquiry([
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
            'user_id'               => auth()->id(),
        ]);
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

