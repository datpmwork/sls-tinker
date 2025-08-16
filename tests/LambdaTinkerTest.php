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
        expect($output)->toEqual("Laravel 10\n3");
    });
});

it('tests wrong variable usage across commands', function () {
    $this->expectTinkerOutput('function', [
        '$a = 1;',
        'echo $c;',
        'echo "a = $a";'
    ], function ($output) {
        expect($output)->toContain("Undefined variable \$c")
            ->and($output)->toContain("a = 1");
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
        expect($output)->toContain("HTTP 404 returned");
    });
});
