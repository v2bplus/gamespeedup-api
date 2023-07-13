<?php

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('vendor', 'public', 'assets')
    ->in(__DIR__)
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        '@PSR12' => true,
        '@PHP74Migration' => true,
        '@PhpCsFixer' => true,
        'single_quote' => true, // 简单字符串应该使用单引号代替双引号;
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true, // 删除空白行的注释
        'no_whitespace_in_blank_line' => true, // 删除空白行最后多余的空格
        'lowercase_cast' => true,
        'no_extra_blank_lines' => ['tokens' => ['break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use']], // 在这些结构体的后面删除空行
        'short_scalar_cast' => true, // boolean -> bool integer->int
        'lowercase_static_reference' => true, // self, static and parent 必须小写.
        'magic_method_casing' => true, // 魔术方法必须小写
        'object_operator_without_whitespace' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_like'],
        'single_line_comment_style' => ['comment_types' => ['asterisk', 'hash']],
        'return_type_declaration' => ['space_before' => 'none'],
        'fully_qualified_strict_types' => true,
        'ternary_to_null_coalescing' => true,
        'cast_spaces' => ['space' => 'single'],
        'combine_consecutive_unsets' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_no_empty_return' => true,
        'no_short_bool_cast' => true,
        'multiline_comment_opening_closing' => true,
        // 'class_attributes_separation' => [['const' => 'only_if_meta', 'method' => 'one', 'property' => 'only_if_meta', 'trait_import' => 'none', 'case' => 'none'],
        // ],
        'combine_consecutive_issets' => true,
        'compact_nullable_typehint' => true,
        'no_alternative_syntax' => true,
        'concat_space' => ['spacing' => 'none'],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'declare_equal_normalize' => true,
        'single_blank_line_before_namespace' => true,
        'no_spaces_around_offset' => true,
        'no_whitespace_before_comma_in_array' => true,
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        'space_after_semicolon' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'return',
                'throw',
                'try',
            ],
        ], // 结构返回前必须加一空行
    ])
    ->setFinder($finder)
    // ->setIndent("\t")
    ->setLineEnding("\n")
    ->setUsingCache(false)
;
