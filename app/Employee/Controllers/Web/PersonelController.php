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
use Illuminate\Http\Request;
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
    public function index(Request $request): View
    {
        $query = Personel::query();

        if ($request->filled('aktif')) {
            $query->where('aktif', $request->aktif);
        }
        if ($request->filled('departman')) {
            $query->where('departman', 'like', '%'.$request->departman.'%');
        }
        if ($request->filled('pozisyon')) {
            $query->where('pozisyon', 'like', '%'.$request->pozisyon.'%');
        }

        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'ad_soyad' => 'ad_soyad',
            'email' => 'email',
            'departman' => 'departman',
            'pozisyon' => 'pozisyon',
            'ise_baslama_tarihi' => 'ise_baslama_tarihi',
            'maas' => 'maas',
            'aktif' => 'aktif',
            'created_at' => 'created_at',
        ];
        if ($sort !== '' && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest();
        }

        $personels = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Personel::count(),
            'active' => Personel::where('aktif', 1)->count(),
        ];

        return view('admin.personnel.index', compact('personels', 'stats'));
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

    /**
     * Apply bulk actions to personnel.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:personels,id'],
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            Personel::whereIn('id', $ids)->delete();
        }
        if ($validated['action'] === 'activate') {
            Personel::whereIn('id', $ids)->update(['aktif' => 1]);
        }
        if ($validated['action'] === 'deactivate') {
            Personel::whereIn('id', $ids)->update(['aktif' => 0]);
        }

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Seçili personel için toplu işlem uygulandı.');
    }
}
