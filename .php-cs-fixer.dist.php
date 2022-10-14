<?php declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->notPath('/^bootstrap\.php$/')
    ->append([__FILE__])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => false,
        'cast_spaces' => ['space' => 'none'],
        'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'one']],
        'comment_to_phpdoc' => [],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => false,
        'linebreak_after_opening_tag' => false,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'allow_unused_params' => false],
        'ordered_class_elements' => ['order' => ['use_trait', 'constant', 'property', 'construct']],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true],
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => ['ignored_tags' => ['var', 'see', 'todo', 'psalm-suppress']],
        'protected_to_private' => false,
        'yoda_style' => false,
        'visibility_required' => ['elements' => ['property', 'method']],
    ])
    ->setFinder($finder)
;
