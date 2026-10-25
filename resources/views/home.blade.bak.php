@extends('layouts.app')

@section('title', 'Beranda · UIN Mart')

@section('content')

@php
    $catIcon = function ($name) {
        $name = strtolower($name);

        if (str_contains($name, 'elektronik')) return 'phone';
        if (str_contains($name, 'fashion')) return 'shirt';
        if (str_contains($name, 'buku')) return 'book';
        if (str_contains($name, 'kuliah')) return 'school';
        if (str_contains($name, 'rumah') || str_contains($name, 'kos')) return 'home';
        if (str_contains($name, 'makanan') || str_contains($name, 'minuman')) return 'food';
        if (str_contains($name, 'olahraga')) return 'ball';

        return 'grid';
    };
@endphp

<!-- ICON SVG -->
<svg class="mart-svg-library"
     xmlns="http://www.w3.org/2000/svg"
     aria-hidden="true"
     focusable="false">

    <symbol id="mart-phone" viewBox="0 0 24 24">
        <rect x="6" y="2.5" width="12" height="19" rx="2"/>
        <path d="M10 5h4M11 18.5h2"/>
    </symbol>

    <symbol id="mart-shirt" viewBox="0 0 24 24">
        <path d="m8 3 4 3 4-3 5 4-3 5-3-2v11H9V10l-3 2-3-5z"/>
    </symbol>

    <symbol id="mart-book" viewBox="0 0 24 24">
        <path d="M12 6c-3-2.4-6-2.6-10-1v15c4-1.6 7-1.4 10 1 3-2.4 6-2.6 10-1V5c-4-1.6-7-1.4-10 1Zm0 0v15"/>
        <path d="M5 9c2-.5 3-.2 4 .4m6 0c1-.6 2-1 4-.4"/>
    </symbol>

    <symbol id="mart-school" viewBox="0 0 24 24">
        <path d="m2 9 10-6 10 6-10 6zM6 12v6c4 3 8 3 12 0v-6M22 9v8"/>
    </symbol>

    <symbol id="mart-home" viewBox="0 0 24 24">
        <path d="M2 11 12 3l10 8M5 10v11h14V10M10 21v-7h4v7"/>
    </symbol>

    <symbol id="mart-food" viewBox="0 0 24 24">
        <path d="M4 3v7c0 2 4 2 4 0V3M6 3v18M16 21V4c3 0 5 3 5 7v4h-5"/>
    </symbol>

    <symbol id="mart-ball" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path d="M4 6c5 1 7 5 8 16M20 6c-5 1-7 5-8 16M2 12h20"/>
    </symbol>

    <symbol id="mart-grid" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
    </symbol>

    <symbol id="mart-list" viewBox="0 0 24 24">
        <path d="M8 6h13M8 12h13M8 18h13M3 6h1M3 12h1M3 18h1"/>
    </symbol>

    <symbol id="mart-cart" viewBox="0 0 24 24">
        <circle cx="9" cy="21" r="1"/>
        <circle cx="19" cy="21" r="1"/>
        <path d="M2 3h2l3 13h13l2-10H5"/>
    </symbol>

</svg>

<div class="mart-shop">

    <!-- SIDEBAR DANH MỤC -->
    <aside class="mart-sidebar" aria-label="Kategori produk">

        <h2>
            <svg class="mart-icon">
                <use href="#mart-list"/>
            </svg>
            Semua Kategori
        </h2>

        <nav class="mart-sidebar-items">

            <a href="{{ route('home') }}"
               class="mart-side-item {{ !$categoryId ? 'active' : '' }}">

                <svg class="mart-icon">