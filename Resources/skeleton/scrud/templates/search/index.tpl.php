{% trans_default_domain '<?= $entity_translation_name ?>' %}
<?php if ($config['prefix_directory']): ?>
{% extends "<?= $config['prefix_directory'] ?>/layout.html.twig" %}
<?php else: ?>
{% extends "base.html.twig" %}
<?php endif; ?>

{% block title %}{{ 'search.title'|trans() }}{% endblock %}

{% block content %}
<?php if ($config['delete']['activate']): ?>
<div id="delete" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal_title" class="modal-title">{{ 'delete.modal_title'|trans() }}</h5>
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
                    <button type="submit" class="btn btn-danger">{{ 'delete.modal_button'|trans() }}</button>
                {{ form_end(form_delete) }}
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'delete.modal_button_cancel'|trans() }}</button>
            </div>
        </div>
    </div>
</div>
<?php endif ?>
<section class="pt-4 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h1>{{ 'search.h1'|trans() }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <p>
<?php if ($config['create']['activate']) : ?>
                    <a href="{{ path('<?= $route_name ?>_create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ 'button.create_title'|trans() }}
                    </a>
<?php endif ?>
                </p>
            </div>
        </div>
        <hr>
<?php if ($config['search']['filter']) : ?>

        {{ include('<?= $templates_path ?>/search/_filter.html.twig') }}
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
    <script>
        $(document).ready(function(){
<?php if ($config['delete']['activate']): ?>
            $('.btn-delete').click(function(){
                var title = $(this).attr('data-title');
                var path = $(this).attr('data-path');
                $('#form_<?= $route_name ?>_delete').attr('action', path);
                $('#modal_body_title').html("{{ 'message.delete_confirm'|trans() }} : <strong>"+title+"</strong>");
            });
<?php endif ?>
<?php if ($config['search']['multi_select']): ?>
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
<?php endif ?>
        });
    </script>
{% endblock %}
<?php endif ?>