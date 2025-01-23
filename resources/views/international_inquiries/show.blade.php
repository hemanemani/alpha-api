@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">International Inquiry Details</h3>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Inquiry Number: {{ $international_inquiry->inquiry_number }}</h5>
            <p><strong>Mobile Number:</strong> {{ $international_inquiry->mobile_number }}</p>
            <p><strong>Inquiry Date:</strong> {{ $international_inquiry->inquiry_date }}</p>
            <p><strong>Product Categories:</strong> {{ $international_inquiry->product_categories }}</p>
            <p><strong>Specific Product:</strong> {{ $international_inquiry->specific_product }}</p>
            <p><strong>Name:</strong> {{ $international_inquiry->name }}</p>
            <p><strong>Location:</strong> {{ $international_inquiry->location }}</p>
            <p><strong>Inquiry Through:</strong> {{ $international_inquiry->inquiry_through }}</p>
            <p><strong>Inquiry Reference:</strong> {{ $international_inquiry->inquiry_reference }}</p>
            <p><strong>1st Contact Date:</strong> {{ $international_inquiry->first_contact_date }}</p>
            <p><strong>1st Response:</strong> {{ $international_inquiry->first_response }}</p>
            <p><strong>2nd Contact Date:</strong> {{ $international_inquiry->second_contact_date }}</p>
            <p><strong>2nd Response:</strong> {{ $international_inquiry->second_response }}</p>
            <p><strong>3rd Contact Date:</strong> {{ $international_inquiry->third_contact_date }}</p>
            <p><strong>3rd Response:</strong> {{ $international_inquiry->third_response }}</p>
            <p><strong>Notes:</strong> {{ $international_inquiry->notes }}</p>
        </div>
    </div>

    <a href="{{ route('international_inquiries.index') }}" class="btn btn-primary mt-3">Back to List</a>
</div>
@endsection
