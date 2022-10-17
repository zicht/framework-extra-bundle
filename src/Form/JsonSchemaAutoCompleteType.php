<?php
/**
 * @copyright Zicht Online <https://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class JsonSchemaAutoCompleteType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('feed_url');
        $resolver->setAllowedTypes('feed_url', 'string');

        $resolver->setDefault('schema', $this->getSchemaGenerator());

        $resolver->setDefault(
            'options',
            [
                'disable_collapse' => true,
                'disable_edit_json' => true,
                'disable_properties' => true,
                'display_required_only' => false,
                'required_by_default' => true,
                'theme' => 'bootstrap4',
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return JsonSchemaType::class;
    }

    /**
     * Returns the json_encoded schema used by the editor to render an input with autocomplete
     *
     * @return \Closure
     */
    private function getSchemaGenerator()
    {
        return function (array $options) {
            return (object)[
                '$schema' => 'http://json-schema.org/draft-07/schema#',
                'title' => $this->translator->trans($options['label'], [], $options['translation_domain']),
                'type' => 'string',
                'format' => 'autocomplete',
                'options' => (object)[
                    'url' => $options['feed_url'],
                    'autocomplete' => (object)[
                        'search' => 'json_feed_search',
                        'getResultValue' => 'json_feed_result',
                        'renderResult' => 'json_feed_render',
                    ],
                ],
            ];
        };
    }
}
