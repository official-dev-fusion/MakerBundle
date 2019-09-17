{% trans_default_domain '<?= $entity_translation_name ?>' %}
<div class="card p-4 mb-4">
    {{ form_start(form_filter) }}
         <div class="align-baseline">
            <div class="row">
                <div class="col-lg-<?php if ($config['search']['pagination']): ?>6<?php else: ?>10<?php endif ?>">
                    {{ form_row(form_filter.search) }}
                </div>
<?php if ($config['search']['pagination']): ?>
                <div class="col-lg-4">
                    {{ form_row(form_filter.number_by_page) }}
                </div>
<?php endif ?>
                <div class="col-lg-2 mt-filter">
                    <div class="form-group">
                        <button id="filter_submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> {{ 'button.filter'|trans() }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {{ form_end(form_filter) }}
</div>