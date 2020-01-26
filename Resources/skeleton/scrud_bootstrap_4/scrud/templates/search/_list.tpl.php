{% trans_default_domain '<?= $file_translation_name ?>' %}

<?php
$entity_fields = $config['search']['fields'];
$col_count = count($entity_fields);
if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']) { $col_count++; }
?>
{% set col_count = <?= $col_count ?> %}

<?php include_once(__DIR__.'/../functions.php') ?>
<?php if ($config['search']['multi_select']): ?>
{% if can_multi_select %}
    {{ form_start(form_batch) }}
    {{ form_errors(form_batch) }}
{% endif %}
<?php endif ?>
<div class="card mt-4 mb-4">
    <div class="card-body">
        <h2 class="card-title">{{ '<?= $name_snake_case ?>.search.h2'|trans() }}</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
<?php if ($config['search']['multi_select']): ?>
                        {% if can_multi_select %}
                            {% set col_count = col_count + 1 %}
                            <th><div class="form-check pl-0"><input type="checkbox" id="select_all" /></div></th>
                        {% endif %}
<?php endif ?>
<?php if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']): ?>
                        <th>{{ 'search.action_th'|trans() }}</th>
<?php endif ?>

<?php foreach ($entity_fields as $field): ?>
                        <th>{{ '<?= $name_snake_case ?>.<?= 'field.'.$field['label_key_trans'] ?>'|trans() }}</th>
<?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
<?php if ($config['search']['multi_select']): ?>
                {% for child in form_batch.<?= $entity_snake_case_plural ?> %}
                    {% set <?= $entity_snake_case ?> = form_batch.<?= $entity_snake_case_plural ?>.vars.choices[child.vars.value].data %}
<?php else: ?>
                {% for <?= $entity_snake_case ?> in <?= $entity_snake_case_plural ?> %}
<?php endif ?>
                    <tr>
<?php if ($config['search']['multi_select']): ?>
                        {% if can_multi_select %}
                            <td>
                                {{ form_row(child, { 'attr': { 'class': 'select'}}) }}
                            </td>
                        {% endif %}
<?php endif ?>
<?php if ($config['read']['activate'] or $config['update']['activate'] or $config['delete']['activate']): ?>
                        <td>
<?php if ($config['read']['activate']): ?>
                            {% if can_read %}
                                <a href="{{ path('<?= $route_name ?>_read', {'<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}" title="{{ 'button.read_title'|trans() }}"
                                    class="btn btn-primary" aria-label="{{ 'button.read_title'|trans() }}" role="button">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                            {% endif %}
<?php endif ?>
<?php if ($config['update']['activate']): ?>
                            {% if can_update %}
<?php if ($config['update']['multi_select']): ?>
                                <a href="{{ path('<?= $route_name ?>_update', {'<?= $entity_identifier_snake_case_plural ?>': {0: <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}}) }}" title="{{ 'button.update_title'|trans() }}"
<?php else: ?>
                                <a href="{{ path('<?= $route_name ?>_update', {'<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}" title="{{ 'button.update_title'|trans() }}"
<?php endif ?>
                                    class="btn btn-warning" aria-label="{{ 'button.update_title'|trans() }}" role="button">
                                    <i class="fas fa-edit"></i>
                                </a>
                            {% endif %}
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
                            {% if can_delete %}
                                <a href="#" class="btn btn-danger btn-delete" data-toggle="modal" data-target="#delete"
                                    data-title="{{ <?= $entity_snake_case ?> }}" role="button"
<?php if ($config['delete']['multi_select']): ?>
                                    data-path="{{ path('<?= $route_name ?>_delete', { '<?= $entity_identifier_snake_case_plural ?>': {0: <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}}) }}"
<?php else: ?>
                                    data-path="{{ path('<?= $route_name ?>_delete', { '<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}"
<?php endif ?>
                                    title="{{ 'button.delete_title'|trans() }}" aria-label="{{ 'button.delete_title'|trans() }}">
                                    <i class="fas fa-times"></i>
                                </a>
                            {% endif %}
<?php endif ?>
                        </td>
<?php endif ?>
<?php foreach ($entity_fields as $field): ?>
                        <td><?= print_field($entity_snake_case, $field) ?></td>
<?php endforeach; ?>
                    </tr>
                {% else %}
                    <tr>
                        <td colspan="{{ col_count }}">{{ 'search.no_data_found'|trans() }}</td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if ($config['search']['multi_select']): ?>
{% if can_multi_select %}
    <div class="card p-2 mt-2">
        <div class="card-body">
            <div class="row">
                <div class="col-md-9 col-lg-9">
                    {{ form_row(form_batch.action) }}
                </div>
                <div class="col-md-3 col-lg-3">
                    <div class="text-center">
                        <button id="submit" class="btn btn-primary btn-block">{{ 'button.validate'|trans() }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ form_end(form_batch) }}
{% endif %}
<?php endif ?>