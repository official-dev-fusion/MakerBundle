<?php
include_once (__DIR__.'/../functions.php');

$prefix = $bag->get('name_snake_case').'.';
$tree->set('yes', 'Yes');
$tree->set('no', 'No');
$tree->set('action.delete', 'Delete');
$tree->set('action.update', 'Update');
$tree->set('search.no_data_found', 'No data found');
if ($config['search']['multi_select']) {
    $tree->set('error.no_element_selected', 'No element selected');
}
if ($config['create']['activate']) {
    $msg = $bag->get('name_human_words_ucfirst').' created (%identifier%).';
    $tree->set($prefix.'create.flash.success', $msg);
    $tree->set('button.create', 'Add');
}
if ($config['update']['activate']) {
    $msg = $bag->get('name_human_words_ucfirst').' updated.';
    $tree->set($prefix.'update.flash.success', $msg);
}
if ($config['delete']['activate']) {
    $tree->set('delete.confirm', 'Confirmation of deletion');
    if ($config['delete']['multi_select']) {
        $msg = $bag->get('name_human_words_ucfirst').'(s) deleted.';
    } elseif ($config['delete']['multi_select']) {
        $msg = $bag->get('name_human_words_ucfirst').' deleted.';
    }
    $tree->set($prefix.'delete.flash.success', $msg);
    $tree->set('button.delete_title', 'Delete');
}
if ($config['create']['activate'] || $config['read']['activate'] || $config['update']['activate'] || $config['delete']['multi_select']) {
    $tree->set('button.back', 'Back');
}
if ($config['create']['activate'] || $config['update']['activate']) {
    $tree->set('button.submit', 'Submit');
}
if ($config['read']['activate']) {
    $tree->set('button.read_title', 'Details');
}
if ($config['update']['activate'] || $config['search']['multi_select']) {
    $tree->set('button.update_title', 'Update');
}
if ($config['search']['multi_select']) {
    $tree->set('button.validate', 'Validate');
}
if ($config['search']['filter_view']['activate']) {
    $tree->set('button.filter', 'Filter');
    $tree->set('label.filter_search', 'Search');
    if ($config['search']['pagination']) {
        $tree->set('label.filter_number_by_page', 'Number by page');
    }
}
if ($config['search']['pagination']) {
    $tree->set('button.previous', 'Previous');
    $tree->set('button.next', 'Next');
}
if ($config['read']['activate'] || $config['update']['activate'] || $config['delete']['activate']) {
    $tree->set('search.action_th', 'Action(s)');
}
if ($config['create']['activate'] || $config['update']['activate']) {
    foreach (get_trans_forms($config) as $key => $value) {
        $tree->set($prefix.'label.'.$key, $value);
    }
}
foreach (get_trans_fields($config) as $key => $value) {
    $tree->set($prefix.'field.'.$key, $value);
}
$msg = 'Search - '.$bag->get('name_human_words_ucfirst');
$tree->set($prefix.'search.title', $msg);
$tree->set($prefix.'search.h1', $msg);
$tree->set($prefix.'search.h2', 'Search');
if ($config['create']['activate']) {
    $msg = 'Create - '.$bag->get('name_human_words_ucfirst');
    $tree->set($prefix.'create.title', $msg);
    $tree->set($prefix.'create.h1', $msg);
    $tree->set($prefix.'create.h2', 'Create form');
}
if ($config['read']['activate']) {
    $msg = 'Details - '.$bag->get('name_human_words_ucfirst');
    $tree->set($prefix.'read.title', $msg);
    $tree->set($prefix.'read.h1', $msg);
    $tree->set($prefix.'read.h2', 'Details');
}
if ($config['update']['activate']) {
    $msg = 'Update - '.$bag->get('name_human_words_ucfirst');
    $tree->set($prefix.'update.title', $msg);
    $tree->set($prefix.'update.h1', $msg);
    $tree->set($prefix.'update.h2', 'Update form');
}
if ($config['delete']['activate']) {
    $msg = 'Delete - '.$bag->get('name_human_words_ucfirst');
    $tree->set($prefix.'delete.modal_title', $msg);
    $tree->set('button.delete', 'Delete');
    $tree->set('button.cancel', 'Cancel');
    $tree->set('delete.warning', 'The deletion will be final.');
}
if ($config['delete']['multi_select']) {
    $msg = 'Delete - '.$bag->get('name_human_words_ucfirst');
    $tree->set($prefix.'delete.h1', $msg);
    $tree->set($prefix.'delete.h2', 'The following list will be deleted');
}
