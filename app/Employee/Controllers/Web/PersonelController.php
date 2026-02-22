<?php

namespace App\Employee\Controllers\Web;

use App\Http\Controllers\Controller;
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

        return view('admin.personnel.index', compact('personels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.personnel.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonelRequest $request): RedirectResponse
    {
        Personel::create($request->validated());

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Personel $personnel): View
    {
        return view('admin.personnel.show', compact('personnel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personel $personnel): View
    {
        return view('admin.personnel.edit', compact('personnel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonelRequest $request, Personel $personnel): RedirectResponse
    {
        $personnel->update($request->validated());

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personel $personnel): RedirectResponse
    {
        $personnel->delete();

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla silindi.');
    }
}
