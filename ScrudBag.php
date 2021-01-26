<?php

namespace DF\MakerBundle;

use Symfony\Component\String\Inflector\EnglishInflector as Inflector;
use Symfony\Bundle\MakerBundle\Str;

/**
 * ScrudBag is a container for key/value pairs.
 */
class ScrudBag implements \IteratorAggregate, \Countable
{
    
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
        $name = Str::asCamelCase($config['name']);
        $this->set('name_upper_camel_case', $name);
        $this->set('name_upper_camel_case_plural', Inflector::pluralize($this->get('name_upper_camel_case'))[0]);
        $this->set('name_lower_camel_case', Str::asLowerCamelCase($name));
        $this->set('name_lower_camel_case_plural', Inflector::pluralize($this->get('name_lower_camel_case'))[0]);
        $this->set('name_snake_case', Str::asSnakeCase($name));
        
        $this->set('name_snake_case_plural', Inflector::pluralize($this->get('name_snake_case'))[0]);
        $this->set('name_human_words', str_replace('_', ' ', $this->get('name_snake_case')));
        $this->set('name_human_words_ucfirst', ucfirst($this->get('name_human_words')));
        $this->set('name_human_words_plural', str_replace('_', ' ', $this->get('name_snake_case_plural')));
        $this->set('name_human_words_plural_ucfirst', ucfirst($this->get('name_human_words_plural')));
        $this->setElement('entity', $entityClassDetails, true);
        $this->set('entity_first_letter', substr($this->get('entity_lower_camel_case'), 0, 1));
        $this->set('entity_identifier_snake_case', $entityDoctrineDetails->getIdentifier());
        $this->set('entity_identifier_snake_case_plural', Inflector::pluralize($entityDoctrineDetails->getIdentifier())[0]);
        $this->set('entity_identifier_lower_camel_case', Str::asLowerCamelCase($entityDoctrineDetails->getIdentifier()));
        $this->set('entity_identifier_lower_camel_case_plural', Inflector::pluralize(Str::asLowerCamelCase($entityDoctrineDetails->getIdentifier()))[0]);
        $this->set('entity_identifier_upper_camel_case', Str::asCamelCase($entityDoctrineDetails->getIdentifier()));
        $this->set('entity_identifier_upper_camel_case_plural', Inflector::pluralize(Str::asCamelCase($entityDoctrineDetails->getIdentifier()))[0]);
        $this->set('entity_human_words', str_replace('_', ' ', $this->get('entity_snake_case')));
        $this->set('entity_human_words_ucfirst', ucfirst($this->get('entity_human_words')));
        $this->set('entity_human_words_plural', str_replace('_', ' ', $this->get('entity_snake_case_plural'))[0]);
        $this->set('entity_human_words_plural_ucfirst', ucfirst($this->get('entity_human_words_plural')));
        if (null !== $repositoryClassDetails) {
            $this->set('repository_full_class_name', $repositoryClassDetails->getFullName());
            $this->set('repository_upper_camel_case', $repositoryClassDetails->getShortName());
            $this->set('repository_lower_camel_case', lcfirst(Inflector::singularize($repositoryClassDetails->getShortName())[0]));
        }
        $this->setConfig();
    }
    
    private function setConfig()
    {
        $this->set('file_translation_name', 'messages');
        $this->set('entity_translation_name', $this->get('name_snake_case'));
        $this->set('route_name', $this->get('name_snake_case'));
        $this->set('route_path', Str::asRoutePath($this->get('name_snake_case')));
        $this->set('templates_path', $this->get('name_snake_case'));
        
        if ($this->get('name_upper_camel_case') !== $this->get('entity_upper_camel_case')) {
            $this->set('search_method', $this->get('name_upper_camel_case'));
            $this->set('search_query_method', $this->get('name_upper_camel_case'));
        }
        
        if ($this->get('config')['prefix_directory']) {
            $prefix = $this->get('config')['prefix_directory'];
            $this->set('file_translation_name', $prefix.'_'.$this->get('file_translation_name'));
            $this->set('entity_translation_name', $prefix.'_'.$this->get('entity_translation_name'));
            $this->set('templates_path', $prefix.'/'.$this->get('templates_path'));
            $this->set('route_name', $prefix.'_'.$this->get('route_name'));
            $this->set('search_method', Str::asCamelCase($prefix).$this->get('search_method'));
            $this->set('search_query_method', Str::asCamelCase($prefix).$this->get('search_query_method'));
        }
        
        $this->set('search_method', 'search'.$this->get('search_method'));
        $this->set('search_query_method', 'get'.$this->get('search_query_method').'Query');
        
        if (isset($this->get('config')['prefix_route']) && $this->get('config')['prefix_route']) {
            $prefix = $this->get('config')['prefix_route'];
            $this->set('route_path', '/'.$prefix.$this->get('route_path'));
        }
        if (isset($this->get('config')['update'])) {
            $this->set('update_form_type', $this->get('entity_upper_camel_case').$this->get('config')['update']['form_type']);
        }
    }

    public function setElement($elementName, $classDetails, $pluralize=false)
    {
        $this->set($elementName.'_full_class_name', $classDetails->getFullName());
        $this->set($elementName.'_upper_camel_case', $classDetails->getShortName());
        $this->set($elementName.'_lower_camel_case', lcfirst(Inflector::singularize($classDetails->getShortName())[0]));
        $this->set($elementName.'_snake_case', Str::asTwigVariable($classDetails->getShortName()));
        if ($pluralize) {
            $this->set($elementName.'_upper_camel_case_plural', Inflector::pluralize($classDetails->getShortName())[0]);
            $this->set($elementName.'_lower_camel_case_plural', lcfirst(Inflector::pluralize($classDetails->getShortName())[0]));
            $this->set($elementName.'_snake_case_plural', Inflector::pluralize(Str::asTwigVariable($classDetails->getShortName()))[0]);
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
    public function set(string $key, $value)
    {
        if (is_array($value) && $key != 'config') {
            
            var_dump($key);
            throw new \Exception();
            die();
        }
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
