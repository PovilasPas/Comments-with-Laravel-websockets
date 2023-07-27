@extends('main') @section('content')
<div class="d-flex vw-100 vh-100 justify-content-center align-items-center">
    <div class="card w-25" style="min-width: 300px">
        <div class="card-body">
            <div class="card-title fs-2 fw-bold">Authenticate</div>
            <hr>
            <form id="authForm" action="/authenticate" method="POST">
                @csrf
                @error('authentication')
                    <div class="text-danger">{{$message}}</div>
                @enderror
                <div class="mb-3">
                    <label for="emailInput" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" id="emailInput" value={{old('email')}}>
                    @error('email')
                        <div class="text-danger">{{$message}}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="passwordInput" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="passwordInput">
                    @error('password')
                        <div class="text-danger">{{$message}}</div>
                    @enderror
                </div>
                <button id="authBtn" class="btn btn-primary w-100">Start commenting</button>
            </form>
        </div>
    </div>
</div>
@endsection