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
                <h1 class="h3">{{ 'read.h1'|trans({ '%identifier%': <?= $entity_snake_case ?> }) }}</h1>
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
                <h2 class="card-title">{{ 'read.h2'|trans() }}</h2>
                <div class="card-content">
                    <ul class="liste-unstyled">
<?php foreach ($entity_fields as $field): ?>
<?php if ($field['field_lower_camel_case'] != $entity_identifier_lower_camel_case): ?>
<?php 
$value = $entity_snake_case.".".$field['field_lower_camel_case'];
if ($field['field_type'] == 'date') {
    $value = $entity_snake_case.".".$field['field_lower_camel_case']." ? "
        .$entity_snake_case.".".$field['field_lower_camel_case']."|localizeddate('medium', 'none') : ''";
} elseif ($field['field_type'] == 'datetime') {
    $value = $entity_snake_case.".".$field['field_lower_camel_case']." ? "
        .$entity_snake_case.".".$field['field_lower_camel_case']."|localizeddate('medium', 'short') : ''";
}
?>
                        <li class="bg-secondary text-light p-2 mb-2 ">
                            <strong>{{ '<?= 'fields.'.$field['field_snake_case'] ?>'|trans() }} : </strong>{{ <?= $value ?> }}
                        </li>
<?php endif; ?>
<?php endforeach; ?>
                    </ul>
                </div>
            </div>        
        </div>
    </div>
</section>
{% endblock %}