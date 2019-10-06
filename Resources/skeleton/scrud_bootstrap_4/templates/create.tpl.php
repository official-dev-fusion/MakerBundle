{% trans_default_domain '<?= $entity_translation_name ?>' %}
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

{% block title %}{{ 'create.title'|trans() }}{% endblock %}

{% block content %}
<section class="pt-4 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="h3">{{ 'create.h1'|trans() }}</h1>
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
        <div class="card mt-4 mb-4">
            <div class="card-body">
                <h2 class="card-title">{{ 'create.h2'|trans() }}</h2>
                <div class="p-4 border border-light">
                    {{ form_start(form) }}
<?php foreach ($entity_form_fields as $field): ?>
                        {{ form_row(form.<?= $field['field_lower_camel_case'] ?>) }}
<?php endforeach; ?>
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