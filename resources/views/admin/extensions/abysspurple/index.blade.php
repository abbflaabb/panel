@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "abysspurple";
    $EXTENSION_NAME = stripslashes("Abyss Purple");
    $EXTENSION_VERSION = "v1.0.0";
    $EXTENSION_DESCRIPTION = stripslashes("The ultimate redesign based on proven high-end themes.");
    $EXTENSION_ICON = "/assets/extensions/abysspurple/icon.jpg";
    $EXTENSION_WEBSITE = "https://blueprint.zip";
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
    @yield('extension.description')<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><strong>Abyss Purple Edition</strong></h3>
            </div>
            <div class="box-body">
                <p>Experience the next level of server management with the Abyss Purple transformation.</p>
                <ul style="font-size: 0.9em; color: #94a3b8;">
                    <li><strong>Glassmorphism</strong>: Advanced translucent UI across all pages.</li>
                    <li><strong>Dynamic Flow</strong>: Animated deep-space gradients.</li>
                    <li><strong>Pro Precision</strong>: High-specificity design overrides.</li>
                </ul>
                <p style="font-size: 0.85em; margin-top: 10px;">
                    Version: <code>v2.0.0-PRO</code> | ID: <code>antigravitytheme</code>
                </p>
                <hr style="margin: 10px 0; border-color: rgba(255,255,255,0.1);">
                <p style="font-size: 0.85em;">Support & Updates via <a href="https://blueprint.zip" target="_blank">blueprint.zip</a></p>
            </div>
        </div>
    </div>
</div>

@endsection
