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

class JsonSchemaType extends AbstractType
{
    /** @var string */
    private $webDir;

    public function __construct(string $webDir)
    {
        $this->webDir = $webDir;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // 'schema' can be:
        // - closure: must return a string or object
        // - object: must be an php object representation of a json-schema
        // - string: must be the public path of a .schema.json file
        // See JsonSchemaType::getSchema
        $resolver->setRequired('schema');
        $resolver->setAllowedTypes('schema', ['closure', 'string', 'object']);

        $resolver->setDefault('popup', false);
        $resolver->setAllowedTypes('popup', 'bool');

        // Options are passed to the json editor: https://github.com/json-editor/json-editor#options
        $resolver->setDefault('options', []);
        $resolver->setAllowedTypes('options', 'array');

        $resolver->setDefault('translation_domain', 'admin');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Transform data to and from a string
        $builder->addModelTransformer(new CallbackTransformer(
            function ($dataAsObject) {
                return json_encode($dataAsObject);
            },
            function ($dataAsString) {
                return json_decode($dataAsString, true);
            }
        ));

        // Validate the data
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            try {
                $this->getSchemaInfo($options)['instance']->in(json_decode($event->getData()));
            } catch (\Exception $exception) {
                $event->getForm()->addError(new FormError($exception->getMessage()));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $schemaInfo = $this->getSchemaInfo($options);
        $view->vars['attr'] = array_filter(
            [
                'class' => 'js-json-editor',
                'data-json-editor-popup' => $options['popup'] ? 'yes' : 'no',
                'data-json-editor-options' => json_encode($options['options']),
                'data-json-editor-schema-url' => $schemaInfo['url'],
                'data-json-editor-schema' => $schemaInfo['encoded'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    private function getSchemaInfo(array $options)
    {
        // closure: must return a string or object
        $schema = is_callable($options['schema']) ? $options['schema']($options) : $options['schema'];

        // object: must be an php object representation of a json-schema
        if (is_object($schema)) {
            $instance = Schema::import($schema);
            return [
                'instance' => $instance,
                'encoded' => json_encode($schema),
                'url' => $instance['$id'],
            ];
        }

        // string: must be the public path of a .schema.json file
        if (is_string($schema)) {
            $instance = Schema::import($this->webDir . $schema);
            return [
                'instance' => $instance,
                'encoded' => null,
                'url' => $instance['$id'],
            ];
        }

        throw new \RuntimeException('$options[\'schema\'] should either be an object, a string, or a callable that returns an object or a string');
    }
}
