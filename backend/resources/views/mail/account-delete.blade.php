@extends('mail.layout')

@section('title', 'Confirm account deletion')
@section('preheader', 'Your TRINITY account deletion code is '.$otp.'. It expires in '.$expiresInMinutes.' minutes.')
@section('headline', 'Confirm deletion')
@section('heading', 'Are you sure?')
@section('copy', 'This action is permanent. Enter this verification code to confirm that you want to delete your TRINITY account.')
@section('expiry', 'This code expires in <strong>'.$expiresInMinutes.' minutes</strong>.')
@section('note', 'If you did not request to delete your account, you can safely ignore this email — nothing will be deleted.')
