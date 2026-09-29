@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "sagatrashbin";
    $EXTENSION_NAME = stripslashes("Trash Bin");
    $EXTENSION_VERSION = "1.4.0";
    $EXTENSION_DESCRIPTION = stripslashes("Move deleted server files to a recoverable trash bin before permanent deletion.");
    $EXTENSION_ICON = "/assets/extensions/sagatrashbin/icon.jpg";
    $EXTENSION_WEBSITE = "https://ga1maz.ru";
    $EXTENSION_WEBICON = "bi bi-link-45deg";
?>
@include('blueprint.admin.template')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    @yield('extension.header')
@endsection

@section('content')
    @yield('extension.config')
    @yield('extension.description')<div style="max-width:760px;margin:20px auto;padding:24px;border-radius:16px;background:linear-gradient(135deg,#0b0814,#161126);border:1px solid #352b4a;color:#ede9fe;box-shadow:0 18px 45px rgba(0,0,0,.28)">
    <div style="display:flex;gap:16px;align-items:center">
        <img src="/assets/extensions/sagatrashbin/saga.jpg" alt="Trash Bin" style="width:64px;height:64px;border-radius:14px;object-fit:cover">
        <div>
            <h2 style="margin:0;color:#f5f3ff">Trash Bin</h2>
            <p style="margin:6px 0 0;color:#c4b5fd">Recover deleted server files before they are permanently removed.</p>
        </div>
    </div>
    <div style="margin-top:22px;padding:16px;border-radius:12px;background:#0f0b1d;border:1px solid #251e35">
        <strong style="color:#a78bfa">How deletion works</strong>
        <p style="margin:8px 0 0;color:#ddd6fe;line-height:1.6">Normal file deletion is redirected to a hidden per-server trash directory. Items can be restored, permanently deleted individually, or removed with Empty Trash. Entries expire after 24 hours.</p>
    </div>
    <p style="margin-top:18px;color:#8b7bb8">Original extension by Ga1maz. Compatibility and reliability fixes prepared for Blueprint beta-2026-08.</p>
</div>

@endsection
