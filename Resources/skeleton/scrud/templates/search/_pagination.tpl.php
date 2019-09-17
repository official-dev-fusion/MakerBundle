{% trans_default_domain '<?= $entity_translation_name ?>' %}
{% if number_page > 1 %}
<div class="text-center m-2">
    <ul class="pagination justify-content-center">
        {% for p in range(1, number_page) %}
            {% if p == page %}
                <li class="page-item active">
                    <span class="page-link">{{ p }}</span>
                </li>
            {% else %}
                <li class="page-item">
                    <a href="{{ path('<?= $route_name ?>_search', { 'page' : p }<?php if ($config['search']['filter']): ?>|merge(query_data)<?php endif ?>) }}" class="page-link">{{ p }}</a>
                </li>
            {% endif %} 
        {% endfor %}
    </ul>
</div>
{% endif %}