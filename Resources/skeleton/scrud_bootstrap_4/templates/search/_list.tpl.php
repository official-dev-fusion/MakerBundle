{% trans_default_domain '<?= $entity_translation_name ?>' %}
<?php if ($config['search']['multi_select']): ?>
{{ form_start(form_update_search) }}
{{ form_errors(form_update_search) }}
<?php endif ?>
<div class="card mt-4 mb-4">
    <div class="card-body">
        <h2 class="card-title">{{ 'search.h2'|trans() }}</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
<?php if ($config['search']['multi_select']): ?>
<?php if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']): ?>
                        <th><div class="form-check pl-0"><input type="checkbox" id="select_all" /></div></th>
                        <th>{{ 'fields.action'|trans() }}</th>
<?php else: ?>
                        <th><div class="form-check"><input type="checkbox" id="select_all" />}</div></th>
<?php endif ?>
<?php else: ?>
<?php if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']): ?>
                        <th>{{ 'fields.action'|trans() }}</th>
<?php endif ?>
<?php endif ?>
<?php foreach ($entity_fields as $field): ?>
<?php if ($field['field_lower_camel_case'] != $entity_identifier_lower_camel_case): ?>
                        <th>{{ '<?= 'fields.'.$field['field_snake_case'] ?>'|trans() }}</th>
<?php endif ?>
<?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
<?php if ($config['search']['multi_select']): ?>
                {% for child in form_update_search.<?= $entity_snake_case_plural ?> %}
                    {% set <?= $entity_snake_case ?> = form_update_search.<?= $entity_snake_case_plural ?>.vars.choices[child.vars.value].data %}
<?php else: ?>
                {% for <?= $entity_snake_case ?> in <?= $entity_snake_case_plural ?> %}
<?php endif ?>
                    <tr>
<?php if ($config['search']['multi_select']): ?>
                        <td>
                            {{ form_row(child, { 'attr': { 'class': 'select'}}) }}
                        </td>
<?php endif ?>
<?php if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']): ?>
                        <td>
<?php if ($config['read']['activate']): ?>
                            <a href="{{ path('<?= $route_name ?>_read', {'<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}" title="{{ 'button.read_title'|trans() }}"
                                class="btn btn-primary" aria-label="{{ 'button.read_title'|trans() }}">
                                <i class="fas fa-file-alt"></i>
                            </a>
<?php endif ?>
<?php if ($config['update']['activate']): ?>
<?php if ($config['update']['multi_select']): ?>
                            <a href="{{ path('<?= $route_name ?>_update', {'<?= $entity_identifier_snake_case_plural ?>': {0: <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}}) }}" title="{{ 'button.update_title'|trans() }}"
<?php else: ?>
                            <a href="{{ path('<?= $route_name ?>_update', {'<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}" title="{{ 'button.update_title'|trans() }}"
<?php endif ?>
                                class="btn btn-warning" aria-label="{{ 'button.update_title'|trans() }}">
                                <i class="fas fa-edit"></i>
                            </a>
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
                            <a href="#" class="btn btn-danger btn-delete" data-toggle="modal" data-target="#delete"
                                data-title="{{ <?= $entity_snake_case ?> }}"
<?php if ($config['delete']['multi_select']): ?>
                                data-path="{{ path('<?= $route_name ?>_delete', { '<?= $entity_identifier_snake_case_plural ?>': {0: <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}}) }}"
<?php else: ?>
                                data-path="{{ path('<?= $route_name ?>_delete', { '<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}"
<?php endif ?>
                                title="{{ 'button.delete_title'|trans() }}" aria-label="{{ 'button.delete_title'|trans() }}">
                                <i class="fas fa-times"></i>
                            </a>
<?php endif ?>
                        </td>
<?php endif ?>
<?php foreach ($entity_fields as $field): ?>
<?php if ($field['field_lower_camel_case'] != $entity_identifier_lower_camel_case): ?>
<?php if ($field['field_type'] == 'date'): ?>
                        <td>{{ <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?> ? <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?>|localizeddate('medium', 'none') : '' }}</td>
<?php elseif ($field['field_type'] == 'datetime'): ?>
                        <td>{{ <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?> ? <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?>|localizeddate('medium', 'short') : '' }}</td>
<?php else: ?>
                        <td>{{ <?= $entity_snake_case .'.'.$field['field_lower_camel_case'] ?> }}</td>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
                    </tr>
                {% else %}
                    <tr>
<?php
$col_count = count($entity_fields);
if ($config['search']['multi_select']) { $col_count++; }
if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']) { $col_count++; }
?>
                        <td colspan="<?= $col_count ?>">{{ 'message.no_data_found'|trans() }}</td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if ($config['search']['multi_select']): ?>
<div class="card p-2 mt-2">
    <div class="card-body">
        <div class="row">
            <div class="col-md-9 col-lg-9">
                {{ form_row(form_update_search.action) }}
            </div>
            <div class="col-md-3 col-lg-3">
                <div class="text-center">
                    <button id="submit" class="btn btn-primary btn-block">{{ 'button.validate_title'|trans() }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
{{ form_end(form_update_search) }}
<?php endif ?>