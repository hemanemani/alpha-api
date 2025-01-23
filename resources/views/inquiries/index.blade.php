@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="my-4">Inquiries</h3>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <table id="example" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Inquiry Number</th>
                    <th>Mobile Number</th>
                    <th>Inquiry Date</th>
                    <th>Specific Product</th>
                     <th>Added By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->inquiry_number }}</td>
                        <td>{{ $inquiry->mobile_number }}</td>
                        <td>{{ $inquiry->inquiry_date }}</td>
                        <td>{{ $inquiry->specific_product }}</td>
                        <td>{{ optional($inquiry->user)->name ?? 'No user assigned' }}</td>

                        <td>
                            <select class="form-select form-select-sm mt-2 update-inquiry-status" data-id="{{ $inquiry->id }}">
                                <option value="" disabled selected>New</option>
                                <option value="1" {{ $inquiry->status === 1 ? 'selected' : '' }}>Approve</option>
                                <option value="0" {{ $inquiry->status === 0 ? 'selected' : '' }}>Cancel</option>
                            </select>
                        </td>


                        <td>
                            <a href="{{ route('inquiries.show', $inquiry->id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('inquiries.edit', $inquiry->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('inquiries.destroy', $inquiry->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            document.querySelectorAll('.update-inquiry-status').forEach(select => {
                select.addEventListener('change', function () {
                    const inquiryId = this.getAttribute('data-id');
                    const status = this.value;

                    fetch(`/inquiries/${inquiryId}/update-inquiry-status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Status updated successfully!');
                            location.reload();
                        } else {
                            alert('Failed to update status.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                });
            });
        });
    </script>




@endsection
