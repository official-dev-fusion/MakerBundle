{% trans_default_domain '<?= $entity_translation_name ?>' %}
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

{% block title %}{{ 'update.title'|trans() }}{% endblock %}

{% block content %}
<section class="pt-4 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h1>{{ 'update.h1'|trans() }}</h1>
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
                    <h2>{{ 'update.h2'|trans() }}</h2>
                </div>
                <div class="card-content m-2">
                    {{ form_start(form) }}
<?php if (!$config['search']['multi_select']): ?>
<?php foreach ($entity_form_fields as $field): ?>
                        {{ form_row(form.<?= $field['field_lower_camel_case'] ?>) }}
<?php endforeach; ?>
<?php else: ?>
                        {{ form_errors(form.<?= $entity_snake_case_plural ?>) }}
                        {% for <?= $entity_snake_case ?>_field in form.<?= $entity_snake_case_plural ?> %}
                            <span class="font-weight-bold">{{ form_label(<?= $entity_snake_case ?>_field) }}</span>
<?php foreach ($entity_form_fields as $field): ?>
                            {{ form_row(<?= $entity_snake_case ?>_field.<?= $field['field_lower_camel_case'] ?>) }}
<?php endforeach; ?>
                        {% endfor %}
<?php endif ?>
                        <button class="btn btn-primary btn-block">
                            <i class="far fa-paper-plane"></i> {{ 'button.submit'|trans() }}
                        </button>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
    </div>
</section>
{% endblock %}