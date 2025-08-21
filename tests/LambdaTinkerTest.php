<?php

it('tests variable persistence across commands', function () {
    $this->expectTinkerOutput('function', [
        '$name = "Laravel";',
        '$version = "10";',
        'echo $name . " " . $version;',
        '$a = 1;',
        '$b = 2;',
        '$c = $a + $b;',
        'echo $c;',
    ], function ($output) {
        expect($output)->toMatchArray([
            '= "Laravel"',
            '= "10"',
            'Laravel 10⏎',
            '= 1',
            '= 2',
            '= 3',
            '3⏎',
        ]);
    });
});

it('tests wrong variable usage across commands', function () {
    $this->expectTinkerOutput('function', [
        '$a = 1;',
        'echo $c;',
        'echo "a = $a";',
    ], function ($output) {
        expect(implode("\n", $output))->toContain('Undefined variable $c.')
            ->toContain('a = 1');
    });
});

it('should fail when lambda function not found', function () {
    $this->expectTinkerOutput('wrong-function', [
        '$name = "Laravel";',
        '$version = "10";',
        'echo $name . " " . $version;',
        '$a = 1;',
        '$b = 2;',
        '$c = $a + $b;',
        'echo $c;',
    ], function ($output) {
        expect(implode("\n", $output))->toContain('HTTP 404 returned');
    });
});
