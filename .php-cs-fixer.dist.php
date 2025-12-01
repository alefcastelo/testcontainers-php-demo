<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'blank_line_after_opening_tag' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'global_namespace_import' => [
            'import_classes' => true,
        ],
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'length',
        ],
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '=' => 'align_single_space_minimal',
                '===' => 'align_single_space_minimal',
                '!==' => 'align_single_space_minimal',
                '&&' => 'align_single_space_minimal',
                '||' => 'align_single_space_minimal',
                '??' => 'align_single_space_minimal',
                '??=' => 'align_single_space_minimal',
            ],
        ],
    ])
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true);
