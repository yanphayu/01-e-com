@extends('mail.layout')

@section('title', 'Verify your email address')
@section('preheader', 'Your TRINITY verification code is '.$otp.'. It expires in '.$expiresInMinutes.' minutes.')
@section('headline', 'Verify your email')
@section('heading', 'One last step')
@section('copy', 'Enter this verification code to confirm your email address and activate your TRINITY account.')
@section('expiry', 'This code expires in <strong>'.$expiresInMinutes.' minutes</strong>.')
@section('note', 'If you did not create this account, you can safely ignore this email.')
