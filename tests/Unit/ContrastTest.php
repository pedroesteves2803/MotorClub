<?php

it('keeps readable contrast for MotorClub text and surfaces in both themes', function () {
    $css = file_get_contents(__DIR__.'/../../resources/css/app.css');
    preg_match('/\.mc-site \{([^}]+)\}/s', $css, $light);
    preg_match('/\.dark \.mc-site, \.mc-site\.dark \{([^}]+)\}/s', $css, $dark);
    $tokens = function ($block) {
        preg_match_all('/--mc-([\w-]+):\s*(#[a-fA-F0-9]{6});/', $block, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn ($match) => [$match[1] => $match[2]])->all();
    };
    $luminance = function ($hex) {
        $rgb = array_map(function ($channel) {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
    };
    foreach ([$tokens($light[1]), array_merge($tokens($light[1]), $tokens($dark[1]))] as $theme) {
        foreach ([['ink', 'paper'], ['ink', 'surface'], ['muted', 'paper'], ['muted', 'surface'], ['orange', 'paper'], ['orange', 'surface'], ['notice-ink', 'notice']] as [$text, $background]) {
            $a = $luminance($theme[$text]);
            $b = $luminance($theme[$background]);
            expect((max($a, $b) + 0.05) / (min($a, $b) + 0.05))->toBeGreaterThanOrEqual(4.5);
        }
        expect((1.05) / ($luminance($theme['action']) + 0.05))->toBeGreaterThanOrEqual(4.5);
    }
});
