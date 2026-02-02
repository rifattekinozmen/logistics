<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rapor Tipleri (başlık setleri)
    |--------------------------------------------------------------------------
    | Her tipin bir anahtarı ve beklenen başlık listesi vardır.
    | Yükleme sırasında seçilen tipe göre normalize yapılır.
    */
    'report_types' => [
        'endustriyel_hammadde' => [
            'label' => 'Endüstriyel Hammadde',
            /*
             * Excel'de bazen "Araç Çıkış Saati" yerine "Araç Çıkış Tarihi" başlığı kullanılıyor.
             * Bu sütunun "Araç Çıkış Saati" olarak eşlenmesi için alias tanımlanır.
             */
            'header_aliases' => [
                'Araç Çıkış Saati' => ['Araç Çıkış Tarihi'],
            ],
            'headers' => [
                'Satınalma belgesi',
                'Kalem',
                'Çerçeve sözleşme',
                'Sözleşme kalemi',
                'Tarih',
                'Doğal sayı',
                'Şirket kodu',
                'Şirket adı',
                'Satıcı',
                'ÜY Tanım',
                'Ocak Kodu',
                'Tanım',
                'Malzeme',
                'Malzeme kısa metni',
                'Dolu Ağırlık',
                'Boş Ağırlık',
                'Geçerli Miktar',
                'Temel ölçü birimi',
                'Geçerli Miktar',
                'Temel ölçü birimi',
                'Parti',
                'Üretim yeri',
                'Ad',
                'Depo yeri',
                'Depo yeri tanımı',
                'Teslimat',
                'Kalem (SD)',
                'Teslimat miktarı',
                'Toplam Rutubet',
                'Rutubet Çarpanı',
                'İrsaliye Seri',
                'İrsaliye No',
                'Araç Giriş Tarihi',
                'Araç Giriş Saati',
                'Araç Çıkış Tarihi',
                'Araç Çıkış Saati',
                'Firma Miktarı',
                'Plaka',
                'FİRMA',
                'Şöför Adı',
                'Nakliye Firma Kodu',
                'Nakliye Firma Adı',
                'Nakliye İrs. Tarihi',
                'Eylem Kodu',
                'Gemi Kodu',
                'Malzeme belgesi',
                'Malzeme belgesi yılı',
                'Belge kalemi',
                'İleti tipi',
            ],
        ],
        'dokme_cimento' => [
            'label' => 'Dökme Çimento',
            'headers' => [
                'Satınalma belgesi',
                'Kalem',
                'Tarih',
                'Şirket kodu',
                'Şirket adı',
                'Malzeme',
                'Malzeme kısa metni',
                'Miktar',
                'Birim',
                'Teslimat',
                'Plaka',
                'Firma',
                'İrsaliye No',
                'Tarih/Saat',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Varsayılan başlıklar (geriye uyumluluk; report_type yoksa kullanılır)
    |--------------------------------------------------------------------------
    */
    'expected_headers' => [
        'Satınalma belgesi',
        'Kalem',
        'Çerçeve sözleşme',
        'Sözleşme kalemi',
        'Tarih',
        'Doğal sayı',
        'Şirket kodu',
        'Şirket adı',
        'Satıcı',
        'ÜY Tanım',
        'Ocak Kodu',
        'Tanım',
        'Malzeme',
        'Malzeme kısa metni',
        'Dolu Ağırlık',
        'Boş Ağırlık',
        'Geçerli Miktar',
        'Temel ölçü birimi',
        'Geçerli Miktar',
        'Temel ölçü birimi',
        'Parti',
        'Üretim yeri',
        'Ad',
        'Depo yeri',
        'Depo yeri tanımı',
        'Teslimat',
        'Kalem (SD)',
        'Teslimat miktarı',
        'Toplam Rutubet',
        'Rutubet Çarpanı',
        'İrsaliye Seri',
        'İrsaliye No',
        'Araç Giriş Tarihi',
        'Araç Giriş Saati',
        'Araç Çıkış Tarihi',
        'Araç Çıkış Saati',
        'Firma Miktarı',
        'Plaka',
        'FİRMA',
        'Şöför Adı',
        'Nakliye Firma Kodu',
        'Nakliye Firma Adı',
        'Nakliye İrs. Tarihi',
        'Eylem Kodu',
        'Gemi Kodu',
        'Malzeme belgesi',
        'Malzeme belgesi yılı',
        'Belge kalemi',
        'İleti tipi',
    ],

];
