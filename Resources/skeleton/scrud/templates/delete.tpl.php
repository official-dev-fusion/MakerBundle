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
                <h1>{{ 'delete.h1'|trans() }}</h1>
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
                    <h2>{{ 'delete.h2'|trans() }}</h2>
                </div>
                <div class="card-content m-2">
                    {{ form_start(form) }}
                        {{ form_errors(form) }}
                        <p class="text-danger">{{ 'delete.warning'|trans() }}</p>
                        <ul class="list-unstyled">
                            {% for <?= $entity_snake_case ?> in <?= $entity_snake_case_plural ?> %}
                                <li>{{ <?= $entity_snake_case ?> }}</li>
                            {% endfor %}
                        </ul>
                        <button class="btn btn-warning btn-block">
                            <i class="far fa-paper-plane"></i> {{ 'button.delete_title'|trans() }}
                        </button>
                    {{ form_end(form) }}
                </div>
            </div>
        </div>
    </div>
</section>
{% endblock %}