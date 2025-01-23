@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Inquiry Details</h3>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Inquiry Number: {{ $inquiry->inquiry_number }}</h5>
            <p><strong>Mobile Number:</strong> {{ $inquiry->mobile_number }}</p>
            <p><strong>Inquiry Date:</strong> {{ $inquiry->inquiry_date }}</p>
            <p><strong>Product Categories:</strong> {{ $inquiry->product_categories }}</p>
            <p><strong>Specific Product:</strong> {{ $inquiry->specific_product }}</p>
            <p><strong>Name:</strong> {{ $inquiry->name }}</p>
            <p><strong>Location:</strong> {{ $inquiry->location }}</p>
            <p><strong>Inquiry Through:</strong> {{ $inquiry->inquiry_through }}</p>
            <p><strong>Inquiry Reference:</strong> {{ $inquiry->inquiry_reference }}</p>
            <p><strong>1st Contact Date:</strong> {{ $inquiry->first_contact_date }}</p>
            <p><strong>1st Response:</strong> {{ $inquiry->first_response }}</p>
            <p><strong>2nd Contact Date:</strong> {{ $inquiry->second_contact_date }}</p>
            <p><strong>2nd Response:</strong> {{ $inquiry->second_response }}</p>
            <p><strong>3rd Contact Date:</strong> {{ $inquiry->third_contact_date }}</p>
            <p><strong>3rd Response:</strong> {{ $inquiry->third_response }}</p>
            <p><strong>Notes:</strong> {{ $inquiry->notes }}</p>
        </div>
    </div>

    <a href="{{ route('inquiries.index') }}" class="btn btn-primary mt-3">Back to List</a>
</div>
@endsection
