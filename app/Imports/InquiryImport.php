<?php

namespace App\Imports;

use App\Models\Inquiry;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class InquiryImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            $inquiry_date = Carbon::createFromFormat('d-m-Y', $row['inquiry_date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $inquiry_date = null;
        }
        
    
        try {
            $first_contact_date = Carbon::createFromFormat('d-m-Y', $row['first_contact_date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $first_contact_date = null;
        }
    
        $second_contact_date = !empty($row['second_contact_date']) 
            ? $this->parseDate($row['second_contact_date']) 
            : null;
    
        $third_contact_date = !empty($row['third_contact_date']) 
            ? $this->parseDate($row['third_contact_date']) 
            : null;
    
        return new Inquiry([
            'inquiry_number'        => $row['inquiry_number'],
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
    
    private function parseDate($date)
    {
        try {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

}

