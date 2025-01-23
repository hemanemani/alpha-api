@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="my-4">Edit International Inquiry</h3>

        <form action="{{ route('international_inquiries.update', $international_inquiry->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="inquiry_number">Inquiry Number</label>
                        <input type="text" name="inquiry_number" id="inquiry_number" class="form-control" value="{{ old('inquiry_number', $international_inquiry->inquiry_number) }}">
                    </div>
                </div>
                    
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="text" name="mobile_number" id="mobile_number" class="form-control" value="{{ old('mobile_number', $international_inquiry->mobile_number) }}">
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="inquiry_date">Inquiry Date</label>
                        <input type="date" name="inquiry_date" id="inquiry_date" class="form-control" value="{{ old('inquiry_date', $international_inquiry->inquiry_date) }}" >
                    </div>
                </div>
                    
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="product_categories">Product Categories</label>
                        <input type="text" name="product_categories" id="product_categories" class="form-control" value="{{ old('product_categories', $international_inquiry->product_categories) }}" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="specific_product">Specific Product</label>
                        <input type="text" name="specific_product" id="specific_product" class="form-control" value="{{ old('specific_product', $international_inquiry->specific_product) }}" >
                    </div>
                </div>
                    
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $international_inquiry->name) }}" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="location">Location (City Name)</label>
                        <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $international_inquiry->location) }}" >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="inquiry_through">Inquiry Through</label>
                        <input type="text" name="inquiry_through" id="inquiry_through" class="form-control" value="{{ old('inquiry_through', $international_inquiry->inquiry_through) }}" >
                    </div>
                 </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="inquiry_reference">Inquiry Reference</label>
                            <input type="text" name="inquiry_reference" id="inquiry_reference" class="form-control" value="{{ old('inquiry_reference', $international_inquiry->inquiry_reference) }}" >
                        </div>
                    </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="first_contact_date">1st Contact Date</label>
                        <input type="date" name="first_contact_date" id="first_contact_date" class="form-control" value="{{ old('first_contact_date', $international_inquiry->first_contact_date) }}" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="first_response">1st Response</label>
                        <textarea name="first_response" id="first_response" class="form-control">{{ old('first_response', $international_inquiry->first_response) }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="second_contact_date">2nd Contact Date</label>
                        <input type="date" name="second_contact_date" id="second_contact_date" class="form-control" value="{{ old('second_contact_date', $international_inquiry->second_contact_date) }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="second_response">2nd Response</label>
                        <textarea name="second_response" id="second_response" class="form-control">{{ old('second_response', $international_inquiry->second_response) }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="third_contact_date">3rd Contact Date</label>
                        <input type="date" name="third_contact_date" id="third_contact_date" class="form-control" value="{{ old('third_contact_date', $international_inquiry->third_contact_date) }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="third_response">3rd Response</label>
                        <textarea name="third_response" id="third_response" class="form-control">{{ old('third_response', $international_inquiry->third_response) }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes" class="form-control">{{ old('notes', $international_inquiry->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-none">
                <div class="form-group mb-3">
                    <label for="user_id">User id</label>
                    <input type="text" name="user_id" id="user_id" class="form-control" value="{{ Auth::check() ? Auth::user()->id : '' }}" readonly>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Inquiry</button>
        </form>
    </div>
@endsection
