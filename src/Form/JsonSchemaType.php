<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Form;

use Swaggest\JsonSchema\Schema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\FrameworkExtraBundle\JsonSchema\SchemaService;

class JsonSchemaType extends AbstractType
{
    /** @var SchemaService */
    private $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        $this->schemaService = $schemaService;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // 'schema' can be:
        // - closure: must return a string or object
        // - object: must be an php object representation of a json-schema
        // - string: must be the public path of a .schema.json file
        // See JsonSchemaType::getSchema
        $resolver->setRequired('schema');
        $resolver->setAllowedTypes('schema', ['closure', 'string', '\\stdClass']);

        $resolver->setDefault('debug', false);
        $resolver->setAllowedTypes('debug', 'bool');

        $resolver->setDefault('popup', false);
        $resolver->setAllowedTypes('popup', 'bool');

        // Options are passed to the json editor: https://github.com/json-editor/json-editor#options
        $resolver->setDefault('options', []);
        $resolver->setAllowedTypes('options', 'array');

        $resolver->setDefault('translation_domain', 'admin');
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Transform data to and from a string
        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($dataAsObject) {
                    return json_encode($dataAsObject);
                },
                function ($dataAsString) {
                    return json_decode($dataAsString, true);
                }
            )
        );

        // Validate the data
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($options) {
                if (!$this->schemaService->validate($this->resolveSchema($options), json_decode($event->getData()), $message)) {
                    $event->getForm()->addError(new FormError($message));
                }
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $schema = $this->resolveSchema($options);
        $url = $schema['$id'];
        $view->vars['attr'] = array_filter(
            [
                'class' => 'js-json-editor',
                'data-json-editor-debug' => $options['debug'] ? 'yes' : 'no',
                'data-json-editor-popup' => $options['popup'] ? 'yes' : 'no',
                'data-json-editor-options' => json_encode($options['options']),
                'data-json-editor-schema-url' => $url,
                'data-json-editor-schema' => $url === null ? json_encode($schema) : null,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    private function resolveSchema(array $options): Schema
    {
        // Closure: must return a string or object
        $schema = is_callable($options['schema']) ? $options['schema']($options) : $options['schema'];

        // Resolve the resulting string or object into a Schema
        return $this->schemaService->getSchema($schema);
    }
}
