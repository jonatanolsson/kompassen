<?php

use App\Models\WcagSuccessCriterion;
use App\Models\WcagRelatedResource;

test('wcag related resource model exists', function () {
    expect(class_exists(WcagRelatedResource::class))->toBeTrue();
});

test('wcag related resource has correct fillable fields', function () {
    $resource = new WcagRelatedResource();

    $fillable = $resource->getFillable();

    expect($fillable)->toContain(
        'wcag_success_criterion_id',
        'type',
        'code',
        'title_en',
        'title_sv',
        'description_en',
        'description_sv',
        'url',
        'order'
    );
});

test('wcag related resource has belongsTo criterion relation', function () {
    $resource = new WcagRelatedResource();

    expect(method_exists($resource, 'wcagSuccessCriterion'))->toBeTrue();
});

test('wcag success criterion has hasMany resources relation', function () {
    $criterion = new WcagSuccessCriterion();

    expect(method_exists($criterion, 'relatedResources'))->toBeTrue();
});

test('wcag related resource uses ulid for id', function () {
    $resource = new WcagRelatedResource();

    expect($resource->getKeyType())->toBe('string');
    expect($resource->getIncrementing())->toBeFalse();
});

test('wcag migration table exists in schema', function () {
    $hasTable = \Illuminate\Support\Facades\Schema::hasTable('wcag_related_resources');

    expect($hasTable)->toBeTrue();
});

test('wcag migration has required columns', function () {
    $hasColumns = \Illuminate\Support\Facades\Schema::hasColumns('wcag_related_resources', [
        'id',
        'wcag_success_criterion_id',
        'type',
        'code',
        'title_en',
        'title_sv',
        'description_en',
        'description_sv',
        'url',
        'order',
        'created_at',
        'updated_at',
    ]);

    expect($hasColumns)->toBeTrue();
});
