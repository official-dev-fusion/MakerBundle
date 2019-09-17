{% trans_default_domain '<?= $entity_translation_name ?>' %}
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

{% block title %}{{ 'read.title'|trans() }}{% endblock %}

{% block content %}
<section class="pt-4 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h1>{{ 'read.h1'|trans({ '%identifier%': <?= $entity_snake_case ?> }) }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <p>
                    <a href="{{ path('<?= $route_name ?>_search') }}" class="btn btn-primary" role="button">
                        <i class="fas fa-reply"></i> {{ 'button.back'|trans() }}
                    </a>
                </p>
            </div>
        </div>
        <hr>
        <div class="col-sm-8 mx-auto">
            <div class="card mt-4 mb-4">
                <div class="card-header text-center">
                    <h2>{{ 'read.h2'|trans() }}</h2>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <tbody>
<?php foreach ($entity_fields as $field): ?>
<?php if ($field['field_lower_camel_case'] != $entity_identifier_lower_camel_case): ?>
                                <tr>
                                    <th>{{ '<?= 'fields.'.$field['field_snake_case'] ?>'|trans() }}</th>
<?php if ($field['field_type'] == 'date'): ?>
                                    <td>{{ <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?> ? <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?>|localizeddate('medium', 'none') : '' }}</td>
<?php elseif ($field['field_type'] == 'datetime'): ?>
                                    <td>{{ <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?> ? <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?>|localizeddate('medium', 'short') : '' }}</td>
<?php else: ?>
                                    <td>{{ <?= $entity_snake_case.'.'.$field['field_lower_camel_case'] ?> }}</td>
<?php endif; ?>
                                </tr>
<?php endif; ?>
<?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
{% endblock %}