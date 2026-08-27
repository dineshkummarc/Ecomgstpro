@extends('layouts.app')

@section('title', 'Guides - EcomgstPro')

@section('content')
<style>
    .guides-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 40px;
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e8edf5;
        text-align: center;
    }
    .guides-title {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 36px;
        color: #0a1628;
        margin-bottom: 16px;
    }
    .guides-text {
        font-size: 16px;
        color: #4a5568;
        line-height: 1.7;
    }
</style>

<div class="guides-container">
    <h1 class="guides-title">EcomgstPro Guides</h1>
    <p class="guides-text">This page is under construction. Comprehensive guides and tutorials will be available here soon to help you master every feature of EcomgstPro.</p>
    <a href="{{ route('home') }}" class="btn btn-primary mt-4" style="background-color: #00c98d; border-color: #00c98d;">← Back to Home</a>
</div>
@endsection