@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex align-items-center justify-between">
            <div>
                <h3 class="my-4">Add New Inquiry</h3>  
            </div>
            <div>
                <a href="{{ route('inquiries.downloadTemplate') }}" class="btn btn-success">
                    Download Template
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                    Bulk Upload Inquiries
                </button>
            </div>
        </div>

        <form action="{{ route('inquiries.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="inquiry_number">Inquiry Number</label>
                        <input type="text" name="inquiry_number" id="inquiry_number" class="form-control">
                    </div>
                </div>
                    
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="text" name="mobile_number" id="mobile_number" class="form-control">
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="inquiry_date">Inquiry Date</label>
                        <input type="date" name="inquiry_date" id="inquiry_date" class="form-control" >
                    </div>
                </div>
                    
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="product_categories">Product Categories</label>
                        <input type="text" name="product_categories" id="product_categories" class="form-control" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="specific_product">Specific Product</label>
                        <input type="text" name="specific_product" id="specific_product" class="form-control" >
                    </div>
                </div>
                    
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="location">Location (City Name)</label>
                        <input type="text" name="location" id="location" class="form-control" >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="inquiry_through">Inquiry Through</label>
                        <input type="text" name="inquiry_through" id="inquiry_through" class="form-control" >
                    </div>
                 </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="inquiry_reference">Inquiry Reference</label>
                            <input type="text" name="inquiry_reference" id="inquiry_reference" class="form-control" >
                        </div>
                    </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="first_contact_date">1st Contact Date</label>
                        <input type="date" name="first_contact_date" id="first_contact_date" class="form-control" >
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="first_response">1st Response</label>
                        <textarea name="first_response" id="first_response" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="second_contact_date">2nd Contact Date</label>
                        <input type="date" name="second_contact_date" id="second_contact_date" class="form-control">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="second_response">2nd Response</label>
                        <textarea name="second_response" id="second_response" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="third_contact_date">3rd Contact Date</label>
                        <input type="date" name="third_contact_date" id="third_contact_date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="third_response">3rd Response</label>
                        <textarea name="third_response" id="third_response" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes" class="form-control"></textarea>
                    </div>
                </div>
                 <div class="col-md-6 d-none">
                    <div class="form-group mb-3">
                        <label for="user_id">User id</label>
                        <input type="text" name="user_id" id="user_id" class="form-control" value="{{ Auth::check() ? Auth::user()->id : '' }}" readonly>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Create Inquiry</button>
        </form>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkUploadModalLabel">Bulk Upload Inquiries</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('inquiries.bulkUpload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Upload Inquiries</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
