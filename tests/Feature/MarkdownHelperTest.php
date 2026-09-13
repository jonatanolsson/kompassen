<?php

use App\Helpers\MarkdownHelper;

test('renders markdown descriptions as html', function () {
    expect(MarkdownHelper::toHtml('**Important**'))->toContain('<strong>Important</strong>');
});

test('preserves flux editor html while sanitizing it', function () {
    $html = '<p>Connection</p><ol><li><p>Everything matters</p></li></ol>';

    expect(MarkdownHelper::toHtml($html))
        ->toContain('<p>Connection</p>')
        ->toContain('<ol>')
        ->toContain('Everything matters');
});
