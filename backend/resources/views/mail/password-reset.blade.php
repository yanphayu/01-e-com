@extends('mail.layout')

@section('title', 'Reset your password')
@section('preheader', 'Your TRINITY password reset code is '.$otp.'. It expires in '.$expiresInMinutes.' minutes.')
@section('headline', 'Reset your password')
@section('heading', 'Forgot your password?')
@section('copy', 'Enter this verification code to choose a new TRINITY password. The code only works for this account.')
@section('expiry', 'This code expires in <strong>'.$expiresInMinutes.' minutes</strong>.')
@section('note', 'If you did not request a password reset, you can safely ignore this email and your password will stay unchanged.')
