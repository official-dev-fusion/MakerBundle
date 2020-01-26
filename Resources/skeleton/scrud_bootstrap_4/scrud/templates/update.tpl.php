{% trans_default_domain '<?= $file_translation_name ?>' %}
<?php
    include_once(__DIR__.'/../functions.php');
    $forms = get_forms($config, 'update');
?>
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

{% block title %}{{ '<?= $name_snake_case ?>.update.title'|trans() }}{% endblock %}

{% block content %}
<section class="pt-4 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="h3">{{ '<?= $name_snake_case ?>.update.h1'|trans() }}</h1>
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
                <h2 class="card-title">{{ '<?= $name_snake_case ?>.update.h2'|trans() }}</h2>
                <div class="card-content p-2 mb-4">
                    {{ form_start(form) }}
<?php if (!$config['search']['multi_select']): ?>
<?php foreach ($forms as $form): ?>
                        {{ form_row(form.<?= $form['property'] ?>) }}
<?php endforeach; ?>
<?php else: ?>
                        {{ form_errors(form.<?= $entity_snake_case_plural ?>) }}
                        {% for <?= $entity_snake_case ?>_field in form.<?= $entity_snake_case_plural ?> %}
                            <div class="p-4 mb-4 border border-light">
                                <span class="font-weight-bold">{{ form_label(<?= $entity_snake_case ?>_field) }}</span>
<?php foreach ($forms as $form): ?>
                                {{ form_row(<?= $entity_snake_case ?>_field.<?= $form['property'] ?>) }}
<?php endforeach; ?>
                            </div>
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