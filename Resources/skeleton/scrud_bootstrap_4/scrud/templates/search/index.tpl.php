{% trans_default_domain '<?= $file_translation_name ?>' %}
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

<?php if ($config['create']['activate']): ?>
{% set can_create = true %}
<?php endif; ?>
<?php if ($config['read']['activate']): ?>
{% set can_read = true %}
<?php endif; ?>
<?php if ($config['update']['activate']): ?>
{% set can_update = true %}
<?php endif; ?>
<?php if ($config['delete']['activate']): ?>
{% set can_delete = true %}
<?php endif; ?>
<?php if ($config['search']['filter_view']['activate']): ?>
{% set can_filter = true %}
<?php endif; ?>
<?php if ($config['search']['multi_select']): ?>
{% set can_multi_select = true %}
<?php endif; ?>

{% block title %}{{ '<?= $name_snake_case ?>.search.title'|trans() }}{% endblock %}

{% block content %}
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
<?php endif ?>
    <section class="pt-4 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="h3">{{ '<?= $name_snake_case ?>.search.h1'|trans() }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <p>
<?php if ($config['create']['activate']) : ?>
                        {% if can_create %}
                            <a href="{{ path('<?= $route_name ?>_create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ 'button.create'|trans() }}
                            </a>
                        {% endif %}
<?php endif ?>
                    </p>
                </div>
            </div>
            <hr>
<?php if ($config['search']['filter_view']['activate']) : ?>

            {% if can_filter %}
                {{ include('<?= $templates_path ?>/search/_filter.html.twig') }}
            {% endif %}
<?php endif ?>

            {{ include('<?= $templates_path ?>/search/_list.html.twig') }}
<?php if ($config['search']['pagination']) : ?>

            {{ include('<?= $templates_path ?>/search/_pagination.html.twig') }}
<?php endif ?>

        </div>
    </section>
{% endblock %}

<?php if ($config['delete']['activate'] or $config['search']['multi_select']): ?>
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
<?php if ($config['search']['multi_select']): ?>

    {% if can_multi_select %}
        <script>
            $(document).ready(function(){
                $("#check_all").click(function(){
                    $('input:checkbox').not(this).prop('checked', this.checked);
                });
                $("#select_all").change(function() {
                    if (this.checked) {
                        $(".select").each(function() {
                            this.checked=true;
                        });
                    } else {
                        $(".select").each(function() {
                            this.checked=false;
                        });
                    }
                });
            });
        </script>
    {% endif %}
<?php endif ?>

{% endblock %}
<?php endif ?>
