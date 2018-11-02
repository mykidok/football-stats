$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'strict_comparison' => true,
    'no_useless_return' => true,
    'no useless_else' => true,
    '@PSR2' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_extra_consecutive_blank_lines'=> ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']
    'no_multiline_whitespace_before_semicolons' => true,
    'no_short_echo_tag' => true,
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => true,
    'declare_strict_types' => true,
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'no_useless_else' => true,
    'phpdoc_indent' => true,
    'phpdoc_no_package' => true,
    'phpdoc_order' => true,
    'phpdoc_separation' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_trim' => true,
    'single_quote' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline_array' => true,
    'trim_array_spaces' => true,
];

$excludes = [
    'vendor',
    'storage',
    'node_modules',
    'phpdocker',
    'template',
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude($excludes)
            ->notName('README.md')
            ->notName('*.xml')
            ->notName('*.yml')
            ->notName('_ide_helper.php')
    );