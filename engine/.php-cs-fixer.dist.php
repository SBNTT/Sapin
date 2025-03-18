<?php

return (new PhpCsFixer\Config())
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('Files')
        ->in([
            __DIR__ . '/src',
        ])
        ->name('*.php')
    )
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP83Migration' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'blank_line_between_import_groups' => false,
        'simple_to_complex_string_variable' => true,
        'native_function_invocation' => true,
        'no_unneeded_control_parentheses' => true,
        'phpdoc_align' => false,
        'phpdoc_summary' => false,
        'phpdoc_line_span' => [
            'property' => 'single',
            'method' => 'single',
            'const' => 'single',
        ],
        'concat_space' => ['spacing' => 'one'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_superfluous_phpdoc_tags' => true,
        'no_trailing_whitespace_in_string' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'phpdoc_separation' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'phpdoc_order' => true,
        'array_syntax' => ['syntax' => 'short'],
        'echo_tag_syntax' => [
            'format' => 'long',
            'long_function' => 'echo',
        ],
        'php_unit_test_annotation' => ['style' => 'annotation'],
        'php_unit_size_class' => ['group' => 'small'],
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_internal_class' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'final_internal_class' => true,
        'increment_style' => ['style' => 'pre'],
        'return_type_declaration' => ['space_before' => 'none'],
        'self_static_accessor' => true,
        'void_return' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
            'always_move_variable' => false,
        ],
        'class_definition' => ['multi_line_extends_each_single_line' => true],
        'heredoc_indentation' => true,
        'single_line_throw' => false,
        'class_attributes_separation' => [
            'elements' => [
                'property' => 'one',
                'method' => 'one',
            ],
        ],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'single_line_empty_body' => true,
        'strict_param' => true,
        PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer::name() => true,
        PhpCsFixerCustomFixers\Fixer\NoPhpStormGeneratedCommentFixer::name() => true,
    ]);
