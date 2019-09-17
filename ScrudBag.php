<?php

namespace DF\MakerBundle;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\MakerBundle\Str;

/**
 * ScrudBag is a container for key/value pairs.
 */
class ScrudBag implements \IteratorAggregate, \Countable
{
    const IGNORE_FORM_FIELDS = [
        'createdAt',
        'updatedAt',
    ];
    
    /**
     * Parameter storage.
     */
    protected $parameters;

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct($entityClassDetails, $doctrineHelper, $repositoryClassDetails, $config)
    {
        $entityDoctrineDetails = $doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
        $this->parameters = [];
        $this->set('config', $config);
        $this->setElement('entity', $entityClassDetails, true);
        $this->set('entity_first_letter', substr($this->get('entity_lower_camel_case'), 0, 1));
        $this->set('entity_identifier_snake_case', $entityDoctrineDetails->getIdentifier());
        $this->set('entity_identifier_snake_case_plural', Inflector::pluralize($entityDoctrineDetails->getIdentifier()));
        $this->set('entity_identifier_lower_camel_case', Str::asLowerCamelCase($entityDoctrineDetails->getIdentifier()));
        $this->set('entity_identifier_lower_camel_case_plural', Inflector::pluralize(Str::asLowerCamelCase($entityDoctrineDetails->getIdentifier())));
        $this->set('entity_identifier_upper_camel_case', Str::asCamelCase($entityDoctrineDetails->getIdentifier()));
        $this->set('entity_identifier_upper_camel_case_plural', Inflector::pluralize(Str::asCamelCase($entityDoctrineDetails->getIdentifier())));
        $this->set('entity_human_words', str_replace('_', ' ', $this->get('entity_snake_case')));
        $this->set('entity_human_words_ucfirst', ucfirst($this->get('entity_human_words')));
        $this->set('entity_human_words_plural', str_replace('_', ' ', $this->get('entity_snake_case_plural')));
        $this->set('entity_human_words_plural_ucfirst', ucfirst($this->get('entity_human_words_plural')));
        if (null !== $repositoryClassDetails) {
            $this->set('repository_full_class_name', $repositoryClassDetails->getFullName());
            $this->set('repository_upper_camel_case', $repositoryClassDetails->getShortName());
            $this->set('repository_lower_camel_case', lcfirst(Inflector::singularize($repositoryClassDetails->getShortName())));           
        }
        $this->setFields($entityClassDetails, $entityDoctrineDetails, $doctrineHelper);
        $this->setConfig();
    }
    
    private function setFields($entityClassDetails, $entityDoctrineDetails, $doctrineHelper)
    {
        $entityMetadata = $doctrineHelper->getMetadata($entityClassDetails->getFullName());
        
        $entityFields = [];
        foreach ($entityDoctrineDetails->getDisplayFields() as $value) {
            $entityFields[] = [
                'field_lower_camel_case' => $value['fieldName'],
                'field_snake_case' => $value['columnName'],
                'field_type' => $value['type'],
            ];
        }
        $this->set('entity_fields', $entityFields);
        $entitySearchFields = [];
        $entityFormFields = [];
        $fieldTypeFullClassNames = [];
        foreach($entityDoctrineDetails->getFormFields() as $key => $value) {
            if (!in_array($key, self::IGNORE_FORM_FIELDS)) {
                $typeField = $entityMetadata->getTypeOfField($key);
                if ('string' == $typeField || 'text' == $typeField) {
                    $entitySearchFields[] = Str::asLowerCamelCase($key);
                }
                $formType = $this->getFormType($key, $typeField);
                if ($formType) {
                    $fieldTypeClass = $formType.'::class';
                    $fieldTypeFullClassName = 'Symfony\\Component\\Form\\Extension\\Core\\Type\\'.$formType;
                    if (!in_array($fieldTypeFullClassName, $fieldTypeFullClassNames)) {
                        $fieldTypeFullClassNames[] = $fieldTypeFullClassName;
                    }
                } else {
                     $fieldTypeClass = 'null';
                }
                
                $entityFormFields[] = [ 
                    'field_snake_case' => Str::asTwigVariable($key),
                    'field_lower_camel_case' => $key,
                    'field_type_class' => $fieldTypeClass,
                ];
            }
        }
        $this->set('entity_search_fields', $entitySearchFields);
        $this->set('field_type_full_class_names', $fieldTypeFullClassNames);
        $this->set('entity_form_fields', $entityFormFields);
    }
    
    private function getFormType($name, $type)
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
            case 'text': return 'TextareaType';
            case 'date': 
                if ('birthday' === $name || 'birthdate' === $name) {
                    return 'BirthdayType';
                } else {
                    return 'DateType';
                }
            case 'datetime': return 'DateTimeType';
            default : return null;
        }
    }
    
    private function setConfig()
    {
        $this->set('entity_translation_name', $this->get('entity_snake_case'));
        $this->set('route_name', $this->get('entity_snake_case'));
        $this->set('route_path', Str::asRoutePath($this->get('entity_snake_case')));
        $this->set('templates_path', $this->get('entity_snake_case'));
        if ($this->get('config')['prefix_directory']) {
            $prefix = $this->get('config')['prefix_directory'];
            $this->set('entity_translation_name', $prefix.'_'.$this->get('entity_translation_name'));
            $this->set('templates_path', $prefix.'/'.$this->get('templates_path'));
            $this->set('route_name', $prefix.'_'.$this->get('route_name'));
        }
        if ($this->get('config')['prefix_route']) {
            $prefix = $this->get('config')['prefix_route'];
            $this->set('route_path', '/'.$prefix.$this->get('route_path'));
        }
    }

    public function setElement($elementName, $classDetails, $pluralize=false)
    {
        $this->set($elementName.'_full_class_name', $classDetails->getFullName());
        $this->set($elementName.'_upper_camel_case', $classDetails->getShortName());
        $this->set($elementName.'_lower_camel_case', lcfirst(Inflector::singularize($classDetails->getShortName())));
        $this->set($elementName.'_snake_case', Str::asTwigVariable($classDetails->getShortName()));
        if ($pluralize) {
            $this->set($elementName.'_upper_camel_case_plural', Inflector::pluralize($classDetails->getShortName()));
            $this->set($elementName.'_lower_camel_case_plural', lcfirst(Inflector::pluralize($classDetails->getShortName())));
            $this->set($elementName.'_snake_case_plural', Inflector::pluralize(Str::asTwigVariable($classDetails->getShortName())));
        }
    }
    
    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys()
    {
        return array_keys($this->parameters);
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param array $parameters An array of parameters
     */
    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds parameters.
     *
     * @param array $parameters An array of parameters
     */
    public function add(array $parameters = [])
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $key     The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has($key)
    {
        return \array_key_exists($key, $this->parameters);
    }

    /**
     * Removes a parameter.
     *
     * @param string $key The key
     */
    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return \count($this->parameters);
    }
}
