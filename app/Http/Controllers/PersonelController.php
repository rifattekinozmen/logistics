<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonelRequest;
use App\Http\Requests\UpdatePersonelRequest;
use App\Models\Personel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PersonelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $query = Personel::query();

        // Filtreleme
        if (request()->has('aktif') && request('aktif') !== '') {
            $query->where('aktif', request('aktif'));
        }

        if (request()->has('departman') && request('departman') !== '') {
            $query->where('departman', 'like', '%'.request('departman').'%');
        }

        if (request()->has('pozisyon') && request('pozisyon') !== '') {
            $query->where('pozisyon', 'like', '%'.request('pozisyon').'%');
        }

        $personels = $query->latest()->paginate(15);

        return view('personel.index', compact('personels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('personel.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonelRequest $request): RedirectResponse
    {
        Personel::create($request->validated());

        return redirect()->route('personel.index')
            ->with('success', 'Personel başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Personel $personel): View
    {
        return view('personel.show', compact('personel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personel $personel): View
    {
        return view('personel.edit', compact('personel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonelRequest $request, Personel $personel): RedirectResponse
    {
        $personel->update($request->validated());

        return redirect()->route('personel.index')
            ->with('success', 'Personel başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personel $personel): RedirectResponse
    {
        $personel->delete();

        return redirect()->route('personel.index')
            ->with('success', 'Personel başarıyla silindi.');
    }
}
