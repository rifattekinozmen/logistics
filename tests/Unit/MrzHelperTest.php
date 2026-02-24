<?php

use App\Helpers\MrzHelper;

it('generates correct check digit for numeric string', function () {
    expect(MrzHelper::checkDigit('970201'))->toBe('9');
    expect(MrzHelper::checkDigit('311227'))->toBe('2');
});

it('generates TD1 MRZ lines in correct format', function () {
    $lines = MrzHelper::td1Lines(
        documentNumber: 'A34H34139',
        tckn: '10268674554',
        dob: '970201',
        sex: 'M',
        expiry: '311227',
        nationality: 'TUR',
        surname: 'Özmen',
        givenNames: 'Rıfat Tekin'
    );

    expect($lines)->toHaveCount(3);
    expect(strlen($lines[0]))->toBe(30);
    expect(strlen($lines[1]))->toBe(30);
    expect(strlen($lines[2]))->toBe(30);

    expect($lines[0])->toStartWith('I<TUR');
    expect($lines[2])->toContain('OZMEN<<RIFAT<TEKIN');
});

it('includes TC number in optional field of line 1', function () {
    $lines = MrzHelper::td1Lines(
        documentNumber: 'A34H34139',
        tckn: '10268674554',
        dob: '970201',
        sex: 'M',
        expiry: '311227',
        nationality: 'TUR',
        surname: 'Test',
        givenNames: 'User'
    );

    expect($lines[0])->toContain('10268674554');
});
