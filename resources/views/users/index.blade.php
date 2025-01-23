@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-between">
        <div>
            <h3 class="mb-4">Users</h3>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Add New User</a>
        </div>
    </div>
    
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Password</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->password }}</td>
                    <td>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">Edit</a>
                         @if ($user->is_admin != 1)
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
</div>

@endsection
