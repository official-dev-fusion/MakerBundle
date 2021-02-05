{% trans_default_domain '<?= $file_translation_name ?>' %}
<?php include_once(__DIR__.'/../functions.php') ?>
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

<?php if ($config['read']['action_up'] or $config['read']['action_down']): ?>
<?php if ($config['update']['activate']): ?>
{% set can_update = true %}
<?php endif; ?>
<?php if ($config['delete']['activate']): ?>
{% set can_delete = true %}
<?php endif; ?>

<?php endif; ?>
{% block title %}{{ '<?= $name_snake_case ?>.read.title'|trans() }}{% endblock %}

{% block content %}
<?php if ($config['read']['action_up'] or $config['read']['action_down']): ?>
<?php if ($config['delete']['activate']): ?>
{% if can_delete %}
    <div id="delete" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modal_title" class="modal-title">{{ '<?= $name_snake_case ?>.delete.modal_title'|trans() }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="X">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="modal_body" class="modal-body">
                    <h6 id="modal_body_title"></h6>
                    <div class="alert alert-warning">
                        <strong>{{ 'delete.warning'|trans() }}</strong>
                    </div>
                </div>
                <div id="modal_footer" class="modal-footer">
                    {{ form_start(form_delete, {'attr': {'id': 'form_<?= $route_name ?>_delete' }}) }}
                        <button type="submit" class="btn btn-danger">{{ 'button.delete'|trans() }}</button>
                    {{ form_end(form_delete) }}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'button.cancel'|trans() }}</button>
                </div>
            </div>
        </div>
    </div>
{% endif %}

<?php endif; ?>
<?php endif; ?>
    <section class="pt-4 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="h3">{{ '<?= $name_snake_case ?>.read.h1'|trans({ '%identifier%': <?= $entity_snake_case ?> }) }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <p>
                        <a href="{{ path('<?= $route_name ?>_search') }}" class="btn btn-primary" role="button">
                            <i class="fas fa-reply"></i> {{ 'button.back'|trans() }}
                        </a>
<?php if ($config['read']['action_up']): ?>
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
<?php endif; ?>
                    </p>
                </div>
            </div>
            <hr>
            <div class="card mt-4 mb-4">
                <div class="card-body">
                    <h2 class="card-title">{{ '<?= $name_snake_case ?>.read.h2'|trans() }}</h2>
                    <div class="card-content">
                        <ul class="list-unstyled">
<?php $entity_fields = $config['read']['fields'] ?>
<?php foreach ($entity_fields as $field): ?>
                            <li class="p-2 mb-2">
                                <strong>{{ '<?= $name_snake_case ?>.<?= 'field.'.$field['label_key_trans'] ?>'|trans() }} : </strong><?= print_field($entity_snake_case, $field) ?>
                            </li>
<?php endforeach; ?>
                        </ul>
                    </div>
                </div>        
            </div>
<?php if ($config['read']['action_down']): ?>
            <p class="text-center">
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
            </p>
<?php endif; ?>
        </div>
    </section>
{% endblock %}
<?php if ($config['read']['action_up'] or $config['read']['action_down']): ?>
<?php if ($config['delete']['activate']): ?>

{% block javascripts %}
    {{ parent() }}
<?php if ($config['delete']['activate']): ?>
    {% if can_delete %}
        <script>
            $(document).ready(function(){
                $('.btn-delete').click(function(){
                    var title = $(this).attr('data-title');
                    var path = $(this).attr('data-path');
                    $('#form_<?= $route_name ?>_delete').attr('action', path);
                    $('#modal_body_title').html("{{ 'delete.confirm'|trans() }} : <strong>"+title+"</strong>");
                });
            });
        </script>
    {% endif %}
<?php endif ?>
{% endblock %}
<?php endif ?>
<?php endif ?>
