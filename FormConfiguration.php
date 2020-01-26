<?php

namespace DF\MakerBundle;

use Exception;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Bundle\MakerBundle\Str;

class FormConfiguration implements ConfigurationInterface
{
    const IGNORE_FORM_FIELDS = [
        'createdAt',
        'updatedAt',
    ];

    /**
     *
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     *
     * @var Generator
     */
    private $generator;
    
    public static $defaultFormOptions = [ 'property', 'type', 'type_options', 'type_class', 'label_key_trans', 'label' ];

    public function __construct(DoctrineHelper $doctrineHelper, Generator $generator)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->generator = $generator;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('form_config');
            
        $forms = $rootNode
            ->children()
                ->arrayNode('forms')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children();
        $this->addEntityNode($forms)
                        ->end()
                    ->end()
                ->end();
        return $treeBuilder;
    }

    public function addEntityNode(&$node)
    {
        $validFields = function ($values) {
            if (!count($values)) {
                return $values;
            }
            foreach ($values as $key => $value) {
                if (!in_array($key, self::$defaultFormOptions)) {
                    throw new \LogicException(sprintf("%s invalid options.", $key));
                }
            }
            foreach (self::$defaultFormOptions as $option) {
                if (!array_key_exists($option, $values)) {
                    throw new Exception(sprintf("The option %s is required (%s).", $option, print_r($values, true)));
                }
            }
            return $values;
        };
        
        $node
            ->scalarNode('entity_class')
                ->isRequired()
                ->validate()->always()
                    ->then(function ($class) {
                        Validator::classExists($class);
                        $classExplode = explode('\\', $class);
                        $className = array_pop($classExplode);
                        Validator::entityExists($className, $this->doctrineHelper->getEntitiesForAutocomplete());
                        return $class;
                    })
                ->end()
            ->end()
            ->scalarNode('skeleton')
                ->isRequired()
            ->end()
            ->scalarNode('prefix_directory')
                ->isRequired()
            ->end()
            ->arrayNode('fields')
                ->variablePrototype()
                    ->validate()
                    ->always()
                    ->then($validFields)
                    ->end()
                ->end()
            ->end()
        ->end()
        ->beforeNormalization()
            ->ifTrue(function ($values) {
                if (!isset($values['entity_class'])) {
                    return false;
                }
                $classExplode = explode('\\', $values['entity_class']);
                $className = array_pop($classExplode);
                
                try {
                    Validator::classExists($values['entity_class']);
                    Validator::entityExists($className, $this->doctrineHelper->getEntitiesForAutocomplete());
                } catch (Exception $e) {
                    return false;
                }
                
                return true;
            })
            ->then(function ($values) {
                $classExplode = explode('\\', $values['entity_class']);
                $className = array_pop($classExplode);
                $entityClassDetails = $this->generator->createClassNameDetails(
                    Validator::entityExists($className, $this->doctrineHelper->getEntitiesForAutocomplete()),
                    'Entity\\'
                );
                $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
                return $this->normalizeData($values, $entityDoctrineDetails);
            })
        ->end();
        
        return $node;
    }

    private function normalizeData(array $values, $entityDoctrineDetails)
    {
        if (!isset($values['skeleton'])) {
            $values['skeleton'] = 'scrud_bootstrap_4';
        }

        if (!isset($values['prefix_directory'])) {
            $values['prefix_directory'] = '';
        }
        
        $values = $this->normalizeFields($values, $entityDoctrineDetails);

        return $values;
    }
    
    private function normalizeFields(array $values, EntityDetails $entityDoctrineDetails)
    {
        $defaultForms = $this->getDefaultForms($entityDoctrineDetails);
        $forms = $values['fields'] ?? null;
        if (!$forms) {
            foreach ($defaultForms as $defaultForm) {
                $forms[] = $defaultForm;
            }
        } else {
            $forms = $this->overwriteForms($defaultForms, $forms);
        }
        
        $values['fields'] = $forms;
        return $values;
    }

    private function getDefaultForms(EntityDetails $entityDoctrineDetails)
    {
        $forms = [];
        $displayForms = $entityDoctrineDetails->getDisplayFields();
        $formFields = array_keys($entityDoctrineDetails->getFormFields());

        foreach ($displayForms as $displayForm) {
            $name = $displayForm['fieldName'];
            if (in_array($name, $formFields)) {
                $type = $this->getDefaultFormType($name, $displayForm['type']);
                $typeFullClassName = $this->getDefaultFormTypeFullClassName($type);
                $forms[$name] = [
                    'property' => $name,
                    'type' => $type,
                    'type_options' => null,
                    'type_class' => $typeFullClassName,
                    'label_key_trans' => Str::asSnakeCase($name),
                    'label' => ucfirst(Str::asHumanWords($name)),
                ];
            }
        }
        return $forms;
    }

    private function getDefaultFormType(string $name, string $type): ?string
    {
        switch ($type) {
            case 'integer': return 'IntegerType';
            case 'string':
                if ('email' === $name) {
                    return 'EmailType';
                } elseif ('url' === $name) {
                    return 'UrlType';
                } elseif ('tel' === $name) {
                    return 'TelType';
                } elseif ('password' === $name) {
                    return 'PasswordType';
                } elseif ('color' === $name) {
                    return 'ColorType';
                } else {
                    return 'TextType';
                }
                
                // no break
            case 'text': return 'TextareaType';
            case 'date':
                if ('birthday' === $name || 'birthdate' === $name) {
                    return 'BirthdayType';
                } else {
                    return 'DateType';
                }
                // no break
            case 'datetime': return 'DateTimeType';
            case 'time': return 'TimeType';
            default: return null;
        }
    }

    private function getDefaultFormTypeFullClassName(?string $type): ?string
    {
        if (null === $type) {
            return null;
        }
        switch ($type) {
            default: return 'Symfony\\Component\\Form\\Extension\\Core\\Type\\'.$type;
        }
    }
    
    private function overwriteForms(array $defaultForms, array $forms)
    {
        foreach ($forms as $key => &$form) {
            if (is_array($form)) {
                $form = $this->overwriteForm($defaultForms, $form);
            } elseif (is_string($form)) {
                if ($defaultForm = ($defaultForms[$form] ?? null)) {
                    $form = $defaultForm;
                } else {
                    $forms[$key] = [
                        'property' => $form,
                    ];
                }
            }
        }
        return $forms;
    }

    private function overwriteForm(array $defaultForms, array $form)
    {
        if ($defaultForm = ($defaultForms[$form['property'] ?? 'null'] ?? null)) {
            foreach ($defaultForm as $key => $value) {
                $form[$key] = $form[$key] ?? $value;
            }
        } elseif (array_key_exists('property', $form)) {
            if (!array_key_exists('type_options', $form)) {
                $form['type_options'] = null;
            }
            $labelKeyTrans = $form['property'];
            $labelKeyTrans = STR::asSnakeCase($labelKeyTrans);
            if (!array_key_exists('label_key_trans', $form)) {
                $form['label_key_trans'] = $labelKeyTrans;
            }
            if (!array_key_exists('label', $form)) {
                $form['label'] = STR::asHumanWords($labelKeyTrans);
            }
        }
        return $form;
    }
}
