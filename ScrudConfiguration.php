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

class ScrudConfiguration implements ConfigurationInterface
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

    public static $defaultFieldOptions = ['property', 'type', 'label_key_trans', 'label', 'twig_filters', ];
    
    public static $defaultFormOptions = [ 'property', 'type', 'type_options', 'type_class', 'label_key_trans', 'label' ];

    public function __construct(DoctrineHelper $doctrineHelper, Generator $generator)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->generator = $generator;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scrud_config');
            
        $entities = $rootNode
            ->children()
                ->arrayNode('entities')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children();
        $this->addEntityNode($entities)
                        ->end()
                    ->end()
                ->end();
        return $treeBuilder;
    }

    public function addEntityNode(&$node)
    {
        $validFields = function ($values) {
            foreach ($values as $key => $value) {
                if (!in_array($key, self::$defaultFieldOptions)) {
                    throw new \LogicException(sprintf("%s invalid options.", $key));
                }
            }
            foreach (self::$defaultFieldOptions as $option) {
                if (!array_key_exists($option, $values)) {
                    throw new Exception(sprintf("The option %s is required (%s).", $option, print_r($values, true)));
                }
            }
            if (null !== $values['twig_filters'] && !is_array($values['twig_filters'])) {
                throw new \LogicException("The twig_filters option must be of type array or null.");
            }
            return $values;
        };
        
        $validForms = function ($values) {
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
            ->scalarNode('class')
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
            ->scalarNode('prefix_route')
                ->isRequired()
            ->end()
            ->booleanNode('voter')
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
            ->arrayNode('forms')
                ->variablePrototype()
                    ->validate()
                    ->always()
                    ->then($validForms)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('search')
                ->isRequired()
                ->children()
                    ->arrayNode('fields')
                        ->variablePrototype()
                            ->validate()
                            ->always()
                            ->then($validFields)
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('dql_filter')->end()
                    ->arrayNode('order')
                        ->variablePrototype()
                            ->validate()
                            ->always()
                            ->then(static function ($values) {
                                foreach ($values as $key => $value) {
                                    if (!in_array($key, [ 'by', 'direction', ])) {
                                        throw new \LogicException(sprintf("%s invalid options.", $key));
                                    }
                                }
                                return $values;
                            })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('filter_view')
                        ->isRequired()
                        ->children()
                            ->booleanNode('activate')
                                ->isRequired()
                            ->end()
                            ->arrayNode('str_fields')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('pagination')
                        ->isRequired()
                    ->end()
                    ->booleanNode('multi_select')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('create')
                ->isRequired()
                ->children()
                    ->booleanNode('activate')
                        ->isRequired()
                    ->end()
                    ->arrayNode('forms')
                        ->variablePrototype()
                            ->validate()
                            ->always()
                            ->then($validForms)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('read')
                ->isRequired()
                ->children()
                    ->arrayNode('fields')
                        ->variablePrototype()
                            ->validate()
                            ->always()
                            ->then($validFields)
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('activate')
                        ->isRequired()
                    ->end()
                    ->booleanNode('action_up')
                        ->isRequired()
                    ->end()
                    ->booleanNode('action_down')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('update')
                ->isRequired()
                ->children()
                    ->booleanNode('activate')
                        ->isRequired()
                    ->end()
                    ->booleanNode('multi_select')
                        ->isRequired()
                    ->end()
                    ->scalarNode('form_type')
                        ->isRequired()
                    ->end()
                    ->arrayNode('forms')
                        ->variablePrototype()
                            ->validate()
                            ->always()
                            ->then($validForms)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('delete')
                ->isRequired()
                ->children()
                    ->booleanNode('activate')
                        ->isRequired()
                    ->end()
                    ->booleanNode('multi_select')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
        ->end()
        ->beforeNormalization()
            ->ifTrue(function ($values) {
                if (!isset($values['class'])) {
                    return false;
                }
                $classExplode = explode('\\', $values['class']);
                $className = array_pop($classExplode);
                try {
                    Validator::classExists($values['class']);
                    Validator::entityExists($className, $this->doctrineHelper->getEntitiesForAutocomplete());
                } catch (Exception $e) {
                    return false;
                }
                return true;
            })
            ->then(function ($values) {
                $classExplode = explode('\\', $values['class']);
                $className = array_pop($classExplode);
                $entityClassDetails = $this->generator->createClassNameDetails(
                    Validator::entityExists($className, $this->doctrineHelper->getEntitiesForAutocomplete()),
                    'Entity\\'
                );
                $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
                return $this->normalizeData($values, $entityDoctrineDetails);
            })
        ->end()
        ->validate()
        ->always()
        ->then(function ($values) {
            if ($values['update']['multi_select']) {
                if (!$values['search']['multi_select']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.update.multi_select must be false if scrud_config.multi_select is false.'
                    );
                }
            }
            if ($values['delete']['multi_select']) {
                if (!$values['search']['multi_select']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.delete.multi_select must be false if scrud_config.multi_select is false.'
                    );
                }
            }
            
            if ($values['update']['multi_select']) {
                if (!$values['update']['activate']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.update.multi_select must be false if scrud_config.update.activate is false.'
                    );
                }
            }
            if ($values['delete']['multi_select']) {
                if (!$values['delete']['activate']) {
                    throw new \LogicException(
                        'Invalid value, scrud_config.delete.multi_select must be false if scrud_config.delete.activate is false.'
                    );
                }
            }
            return $values;
        });
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

        if (!isset($values['prefix_route'])) {
            $values['prefix_route'] = '';
        }

        if (!isset($values['voter'])) {
            $values['voter'] = false;
        }
        
        if (!isset($values['search'])) {
            $values['search'] = [];
        }

        if (!isset($values['search']['pagination'])) {
            $values['search']['pagination'] = true;
        }

        if (!isset($values['search']['multi_select'])) {
            $values['search']['multi_select'] = true;
        }

        if (!isset($values['search']['filter_view'])) {
            $values['search']['filter_view'] = [];
        }
        
        if (!isset($values['search']['filter_view']['activate'])) {
            $values['search']['filter_view']['activate'] = true;
        }

        if (!isset($values['search']['dql_filter'])) {
            $values['search']['dql_filter'] = '';
        }

        if (!isset($values['search']['order'])) {
            $values['search']['order'] = [];
        }

        if (0 === count($values['search']['order'])) {
            $values['search']['order'] = [
                [
                    'by' => 'entity.'.$entityDoctrineDetails->getIdentifier(),
                    'direction' => 'DESC',
                ]
            ];
        }

        if (!isset($values['create'])) {
            $values['create'] = [];
        }

        if (!isset($values['create']['activate'])) {
            $values['create']['activate'] = true;
        }

        if (!isset($values['read'])) {
            $values['read'] = [];
        }

        if (!isset($values['read']['activate'])) {
            $values['read']['activate'] = true;
        }
        
        if (!isset($values['read']['action_up'])) {
            $values['read']['action_up'] = false;
        }
        
        if (!isset($values['read']['action_down'])) {
            $values['read']['action_down'] = false;
        }

        if (!isset($values['update'])) {
            $values['update'] = [];
        }

        if (!isset($values['update']['activate'])) {
            $values['update']['activate'] = true;
        }

        if (!isset($values['update']['multi_select'])) {
            $values['update']['multi_select'] = false;
        }

        if (!isset($values['delete'])) {
            $values['delete'] = [];
        }

        if (!isset($values['delete']['activate'])) {
            $values['delete']['activate'] = true;
        }

        if (!isset($values['delete']['multi_select'])) {
            $values['delete']['multi_select'] = true;
        }

        $values = $this->normalizeFields($values, $entityDoctrineDetails);
        $values = $this->normalizeForms($values, $entityDoctrineDetails);

        return $values;
    }

    private function normalizeFields(array $values, EntityDetails $entityDoctrineDetails)
    {
        $defaultFields = $this->getDefaultFields($entityDoctrineDetails);
        
        $fields = $values['fields'] ?? null;
        
        if (!$fields) {
            foreach ($defaultFields as $defaultField) {
                $fields[] = $defaultField;
            }
        } else {
            $fields = $this->overwriteFields($defaultFields, $fields);
        }
        
        $searchFields = $values['search']['fields'] ?? null;
        if (!$searchFields) {
            $searchFields = $fields;
        } else {
            $searchFields = $this->overwriteFields($defaultFields, $searchFields);
        }

        $readFields = $values['read']['fields'] ?? null;
        if (!$readFields) {
            $readFields = $fields;
        } else {
            $readFields = $this->overwriteFields($defaultFields, $readFields);
        }
        
        $values['fields'] = $fields;
        $values['search']['fields'] = $searchFields;
        $values['read']['fields'] = $readFields;
        
        $stringFields = [];
        foreach ($searchFields as $field) {
            if ($field['type'] === 'string' || $field['type'] === 'text') {
                $stringFields[] = $field['property'];
            }
        }
        $strFields = $values['search']['filter_view']['strFields'] ?? [];
        if (0 === count($strFields)) {
            $values['search']['filter_view']['str_fields'] = $stringFields;
        }
        return $values;
    }

    private function getDefaultFields(EntityDetails $entityDoctrineDetails)
    {
        $fields = [];
        $displayFields = $entityDoctrineDetails->getDisplayFields();
        foreach ($displayFields as $displayField) {
            $fields[$displayField['fieldName']] = [
                'property' => $displayField['fieldName'],
                'type' => $displayField['type'],
                'label_key_trans' => Str::asSnakeCase($displayField['fieldName']),
                'label' => ucfirst(Str::asHumanWords($displayField['fieldName'])),
                'twig_filters' => $this->getDefaultTwigFilters($displayField['type']),
            ];
        }
        return $fields;
    }

    private function getDefaultTwigFilters($type)
    {
        switch ($type) {
            case 'text':
                return [ "nl2br", ];
            case 'datetime':
                return [ "format_datetime", ];
            case 'date':
                return [ "format_date", ];
            case 'time':
                return [ "format_time", ];
            case 'decimal':
                return [ "format_number", ];
        }
        return null;
    }

    private function overwriteFields(array $defaultFields, array $fields)
    {
        foreach ($fields as $key => &$field) {
            if (is_array($field)) {
                $field = $this->overwriteField($defaultFields, $field);
            } elseif (is_string($field)) {
                if ($defaultField = ($defaultFields[$field] ?? null)) {
                    $field = $defaultField;
                } else {
                    $labelKeyTrans = $field;
                    $label = $labelKeyTrans;
                    if (strpos($label, '.')) {
                        $label = explode('.', $label);
                        $label = array_pop($label);
                        $label = ucfirst(Str::asHumanWords($label));
                    }
                    $labelKeyTrans = STR::asSnakeCase(str_replace('.', '_', $labelKeyTrans));
                    $fields[$key] = [
                        'property' => $field,
                        'type' => null,
                        'label_key_trans' => $labelKeyTrans,
                        'label' => $label,
                        'twig_filters' => [],
                    ];
                }
            }
        }
        return $fields;
    }

    private function overwriteField(array $defaultFields, array $field)
    {
        if ($defaultField = ($defaultFields[$field['property'] ?? 'null'] ?? null)) {
            foreach ($defaultField as $key => $value) {
                $field[$key] = $field[$key] ?? $value;
            }
        } elseif (array_key_exists('property', $field)) {
            if (!array_key_exists('type', $field)) {
                $field['type'] = null;
            }
            $labelKeyTrans = $field['property'];
            $label = $labelKeyTrans;
            if (strpos($label, '.')) {
                $label = explode('.', $label);
                $label = array_pop($label);
                $label = ucfirst(Str::asHumanWords($label));
            }
            $labelKeyTrans = STR::asSnakeCase(str_replace('.', '_', $labelKeyTrans));
            if (!array_key_exists('label_key_trans', $field)) {
                $field['label_key_trans'] = $labelKeyTrans;
            }
            if (!array_key_exists('label', $field)) {
                $field['label'] = $label;
            }
            if (!array_key_exists('label', $field)) {
                $field['label'] = Str::asHumanWords($labelKeyTrans);
            }
            if (!array_key_exists('twig_filters', $field)) {
                $field['twig_filters'] = [];
            }
        }
        return $field;
    }

    private function normalizeForms(array $values, EntityDetails $entityDoctrineDetails)
    {
        $defaultForms = $this->getDefaultForms($entityDoctrineDetails);
        $forms = $values['forms'] ?? null;
        $createForms = $values['create']['forms'] ?? null;
        if ($createForms) {
            $createForms = $this->overwriteForms($defaultForms, $createForms);
        }

        $updateForms = $values['update']['forms'] ?? null;
        if ($updateForms) {
            $updateForms = $this->overwriteForms($defaultForms, $updateForms);
            $values['update']['form_type'] = 'UpdateType';
        } else {
            $values['update']['form_type'] = 'Type';
        }

        if (!$forms) {
            foreach ($defaultForms as $defaultForm) {
                $forms[] = $defaultForm;
            }
        } else {
            $forms = $this->overwriteForms($defaultForms, $forms);
        }
        
        $values['forms'] = $forms;
        $values['create']['forms'] = $createForms;
        $values['update']['forms'] = $updateForms;
        
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
