<?php

namespace App\Employee\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonelRequest;
use App\Http\Requests\UpdatePersonelRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\Personel;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PersonelController extends Controller
{
    /**
     * Form için gerekli referans verileri.
     *
     * @return array<string, mixed>
     */
    private function getFormData(): array
    {
        $key = 'personnel_form_data';
        if (app()->environment('testing')) {
            return $this->buildFormData();
        }

        return Cache::remember($key, 300, fn () => $this->buildFormData());
    }

    /**
     * Form referans verilerini oluşturur.
     *
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        $countries = Country::where('is_active', true)->orderBy('name_tr')->get();
        $cities = \App\Models\City::where('is_active', true)->orderBy('name_tr')->get(['id', 'name_tr', 'country_id']);
        $districts = \App\Models\District::where('is_active', true)->orderBy('name_tr')->get(['id', 'name_tr', 'city_id']);

        return [
            'countries' => $countries,
            'cities' => $cities,
            'districts' => $districts,
            'departments' => Department::select('name')->distinct()->orderBy('name')->pluck('name', 'name'),
            'positions' => Position::select('name')->distinct()->orderBy('name')->pluck('name', 'name'),
            'banks' => config('personnel.banks', []),
        ];
    }

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
        return view('admin.personnel.create', $this->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('personnel-photos', 'public');
        }
        unset($data['photo']);

        Personel::create($data);

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Personel $personnel): View
    {
        $personnel->load(['country', 'city', 'district']);
        $personnel->loadCount(['documents', 'personnelAttendances']);

        return view('admin.personnel.show', compact('personnel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personel $personnel): View
    {
        $personnel->load(['country', 'city', 'district']);
        $personnel->loadCount(['documents', 'personnelAttendances']);

        return view('admin.personnel.edit', array_merge(
            $this->getFormData(),
            ['personnel' => $personnel]
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonelRequest $request, Personel $personnel): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            if ($personnel->photo_path) {
                Storage::disk('public')->delete($personnel->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('personnel-photos', 'public');
        }
        unset($data['photo']);

        $personnel->update($data);

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
