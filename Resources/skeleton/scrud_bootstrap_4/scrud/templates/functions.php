<?php

    function twig_filters (?array $filters): string
    {
        $filters_to_str = "";
        foreach ($filters as $filter) {
            $filters_to_str .= "|" .$filter;
        }
        return $filters_to_str;
    }

    function print_field($entity_snake_case, $field)
    {
        $property = $entity_snake_case . '.' . $field['property'];
        if ($field['twig_filters']) {
            return "{{ " . $property . " ? " . $property . twig_filters($field['twig_filters']) . " : '' }}";
        } elseif ('boolean' === $field['type']) {
            return "{{ " . $property . " ? 'yes'|trans : 'no'|trans }}";
        } elseif (false !== \strpos($property, 'email')) {
            return "<a href=\"mailto:{{ ".$property." }}\">{{ ".$property." }}</a>";
        } elseif (false !== \strpos($property, 'phone')) {
            return "<a href=\"tel:{{ ".$property." }}\">{{ ".$property." }}</a>";
        }
        return "{{ " . $property . " }}";
    }
    
    function get_href(string $route_name, array $config, string $page)
    {
        $href = "{{ path('";
        $href .= $route_name;
        $href .= "_search', { 'page' : $page }";
        if ($config['search']['filter_view']['activate']) {
            $href .= "|merge(query_data)";
        }
        $href .= ") }}";
        return $href;
    }
?>