@extends('layouts.app')

@section('title', 'Kelola Berkas - ' . ($visaForm->nama_lengkap ?? 'Visa Form'))

@section('content')
    <livewire:form.visa-form-attachment-manager :visa-form-id="$visaFormId" />
@endsection

