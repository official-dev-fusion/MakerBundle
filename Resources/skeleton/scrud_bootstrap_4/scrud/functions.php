<?php
    
    function get_forms(array $config, string $scrud_elment): array
    {
        $forms = $config[$scrud_elment]['forms'];
        if (count($forms)) {
            return $forms;
        }
        return $config['forms'];
    }

    function get_trans_forms(array $config): array
    {
        $trans=[];
        $array=[];
        if (0 === count($config['create']['forms']) && 0 === count($config['update']['forms'])) {
            $array = $config['forms'];
        } elseif (count($config['create']['forms']) && count($config['update']['forms'])) {
            $array = array_merge($config['create']['forms'], $config['update']['forms']);
        } elseif (count($config['create']['forms'])) {
            $array = array_merge($config['forms'], $config['create']['forms']);
        } elseif (count($config['update']['forms'])) {
            $array = array_merge($config['forms'], $config['update']['forms']);
        }
        foreach ($array as $form) {
            if (!array_key_exists($form['label_key_trans'], $trans)) {
                $trans[$form['label_key_trans']] = $form['label'];
            }
        }
        return $trans;
    }

    function get_trans_fields(array $config): array
    {
        $trans=[];
        $array=[];
        if (0 === count($config['search']['fields']) && 0 === count($config['read']['fields'])) {
            $array = $config['fields'];
        } elseif (count($config['search']['fields']) && count($config['read']['fields'])) {
            $array = array_merge($config['search']['fields'], $config['read']['fields']);
        } else {
            $array = $config['fields'];
        }
        foreach ($array as $field) {
            if (!array_key_exists($field['label_key_trans'], $trans)) {
                $trans[$field['label_key_trans']] = $field['label'];
            }
        }
        return $trans;
    }

    function get_type_full_class_names ($forms)
    {
        $type_full_class_names = [];
        foreach ($forms as $form) {
            if (!$form['type_class'] && $form['type']) {    
                $form['type_class'] = "Symfony\\Component\\Form\\Extension\\Core\\Type\\" . $form['type'];
            }
            if ($form['type_class'] && !in_array($form['type_class'], $type_full_class_names)) {
                $type_full_class_names[] = $form['type_class'];
            }
        }
        sort($type_full_class_names, SORT_NATURAL | SORT_FLAG_CASE);
        
        return $type_full_class_names;
    }
?>