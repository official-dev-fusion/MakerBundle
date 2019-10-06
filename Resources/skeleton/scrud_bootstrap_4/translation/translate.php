<?php
$translation['action']['delete'] = 'Delete';
$translation['action']['update'] = 'Update';
$translation['message']['no_data_found'] = 'No data found';
if ($config['search']['multi_select']) {
    $translation['message']['no_element_selected'] = 'No element selected';
}
if ($config['create']['activate']) {
    $translation['message']['create'] = $bag->get('entity_human_words_ucfirst').' created (%identifier%).';
}
if ($config['update']['activate']) {
    $translation['message']['update'] = $bag->get('entity_human_words_ucfirst').' updated.';
}
if ($config['delete']['activate']) {
    $translation['message']['delete_confirm'] = 'Confirmation of deletion';
    if ($config['delete']['multi_select']) {
        $translation['message']['delete_list'] = $bag->get('entity_human_words_ucfirst').'(s) deleted.';
    } elseif ($config['delete']['multi_select']) {
        $translation['message']['delete'] = $bag->get('entity_human_words_ucfirst').' deleted.';
    }
}
if ($config['create']['activate'] || $config['read']['activate'] || $config['update']['activate'] || $config['delete']['multi_select']) {
    $translation['button']['back'] = 'Back';
}
if ($config['create']['activate'] || $config['update']['activate']) {
    $translation['button']['submit'] = 'Submit';
}
if ($config['create']['activate']) {
    $translation['button']['create_title'] = 'Add';
}
if ($config['read']['activate']) {
    $translation['button']['read_title'] = 'Read';
}
if ($config['update']['activate'] || $config['search']['multi_select']) {
    $translation['button']['update_title'] = 'Update';
}
if ($config['search']['multi_select']) {
    $translation['button']['validate_title'] = 'Validate';
}
if ($config['delete']['activate']) {
    $translation['button']['delete_title'] = 'Delete';
}
if ($config['search']['filter']) {
    $translation['button']['filter'] = 'Filter';
    $translation['form_labels']['search'] = 'Search';
    if ($config['search']['pagination']) {
        $translation['form_labels']['number_by_page'] = 'Number by page';
    }
}
if ($config['search']['pagination']) {
    $translation['pagination']['previous'] = 'Previous';
    $translation['pagination']['next'] = 'Next';
}    
if ($config['read']['activate'] || $config['update']['activate'] || $config['delete']['activate']) {
    $translation['fields']['action'] = 'Action(s)';
}
foreach ($bag->get('entity_form_fields') as $field) {
    $translation['form_labels'][$field['field_snake_case']] = ucfirst(str_replace('_', ' ', $field['field_snake_case']));
}
foreach ($bag->get('entity_fields') as $field) {
    $translation['fields'][$field['field_snake_case']] = ucfirst(str_replace('_', ' ', $field['field_snake_case']));
}
$translation['search']['title'] = 'Search - '.$bag->get('entity_human_words_ucfirst');
$translation['search']['h1'] = 'Search - '.$bag->get('entity_human_words_ucfirst');
$translation['search']['h2'] = 'Search';
if ($config['create']['activate']) {
    $translation['create']['title'] = 'Create - '.$bag->get('entity_human_words_ucfirst');
    $translation['create']['h1'] = 'Create - '.$bag->get('entity_human_words_ucfirst');
    $translation['create']['h2'] = 'Create form';
}
if ($config['read']['activate']) {
    $translation['read']['title'] = 'Read - '.$bag->get('entity_human_words_ucfirst');
    $translation['read']['h1'] = '%identifier%';
    $translation['read']['h2'] = 'Details';
}
if ($config['update']['activate']) {
    $translation['update']['title'] = 'Update - '.$bag->get('entity_human_words_ucfirst');
    $translation['update']['h1'] = 'Update - '.$bag->get('entity_human_words_ucfirst');
    $translation['update']['h2'] = 'Update form';
}
if ($config['delete']['activate']) {
    $translation['delete']['modal_title'] = 'Delete - '.$bag->get('entity_human_words_ucfirst');
    $translation['delete']['modal_button'] = 'Delete';
    $translation['delete']['modal_button_cancel'] = 'Cancel';
    $translation['delete']['warning'] = 'The deletion will be final.';
}
if ($config['delete']['multi_select']) {
    $translation['delete']['h1'] = 'Delete - '.$bag->get('entity_human_words_ucfirst');
    $translation['delete']['h2'] = 'The following list will be deleted';
}
?>