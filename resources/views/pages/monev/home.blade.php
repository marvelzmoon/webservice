@extends('layouts.layout')

@section('main')
    <p class="fs-2">SISTEM BYPASS MONITORING</p>
    <div class="row align-items-center">
        <div class="col-auto">
            <a href="{{ route('monev.antrolterdaftar') }}">
                <button type="button" class="btn btn-primary btn-lg">Antrian Terdaftar</button>
            </a>
        </div>
    </div>    
@endsection