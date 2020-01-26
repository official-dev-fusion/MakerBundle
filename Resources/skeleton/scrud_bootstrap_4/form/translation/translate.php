<?php

$prefix = $bag->get('name_snake_case').'.';
foreach ($config['fields'] as $value) {
    $tree->set($prefix.'label.'.$value['label_key_trans'], $value['label']);
}
