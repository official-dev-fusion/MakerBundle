<?php include_once(__DIR__.'/../functions.php'); ?>
{% trans_default_domain '<?= $file_translation_name ?>' %}
{% if number_page > 1 %}
    {% if page > 1 %}
        {% set previous = page - 1 %}
    {% endif %}
    {% if page < number_page %}
        {% set next = page + 1 %}
    {% endif %}
    {% set range_start = page - 2 %}
    {% if range_start < 1 %}
        {% set range_start = 1 %}
    {% endif %}
    {% set range_end = page + 2 %}
    {% if range_end > number_page %}
        {% set range_end = number_page %}
    {% endif %}
    <div class="mt-4">
        <nav>
            <ul class="pagination justify-content-center pagination-sm">
                {% if previous is defined %}
                    <li class="page-item">
                        <a class="page-link" rel="prev" href="<?= get_href($route_name, $config, 'previous') ?>">&laquo;&nbsp;{{ 'button.previous'|trans() }}</a>
                    </li>
                {% else %}
                    <li class="page-item disabled">
                        <span class="page-link">&laquo;&nbsp;{{ 'button.previous'|trans() }}</span>
                    </li>
                {% endif %}
                {% if range_start > 1 %}
                    <li class="page-item">
                        <a class="page-link" href="<?= get_href($route_name, $config, '1') ?>">1</a>
                    </li>
                    {% if range_start == 3 %}
                        <li class="page-item">
                            <a class="page-link" href="<?= get_href($route_name, $config, '2') ?>">2</a>
                        </li>
                    {% elseif range_start != 2 %}
                        <li class="page-item disabled">
                            <span class="page-link">&hellip;</span>
                        </li>
                    {% endif %}
                {% endif %}
                {% set pages = [] %}
                {% if (range_start -1) > 10 %}
                    {% set ten = range_start - range_start % 10 %}
                    {% for i in 1..3 %}
                        {% if ten > 1 %}
                            {% if ten != range_start %}
                                {% set pages = ([ten]|merge(pages)) %}
                            {% endif %}
                            {% set ten = (range_start - i * 10) - (range_start - i * 10) % 10 %}
                        {% endif %}
                    {% endfor %}
                    
                    {% if ten > 100 %}
                        {% set hundred = (ten) - (ten) % 100 %}
                        {% for i in 1..3 %}
                            {% if hundred > 1 %}
                                {% set pages = [hundred]|merge(pages) %}
                                {% set hundred = (ten - i * 100) - (ten - i * 100) % 100 %}
                            {% endif %}
                        {% endfor %}
                    {% endif %}
                {% endif %}
                
                {% for p in pages %}
                    <li class="page-item">
                        <a class="page-link" href="<?= get_href($route_name, $config, 'p') ?>">{{ p }}</a>
                    </li>
                {% endfor %}
                {% for p in range_start..range_end %}
                    {% if p == page %}
                        <li class="page-item active">
                            <span class="page-link">{{ p }}</span>
                        </li>
                    {% else %}
                        <li class="page-item">
                            <a class="page-link" href="<?= get_href($route_name, $config, 'p') ?>">{{ p }}</a>
                        </li>
                    {% endif %} 
                {% endfor %}
                
                {% if (number_page - range_end) > 10 %}
                    {% set ten = (range_end + 10) - (range_end + 10) % 10 %}
                    
                    {% for i in 2..4 %}
                        {% if ten < number_page %}
                            <li class="page-item">
                                <a class="page-link" href="<?= get_href($route_name, $config, 'ten') ?>">{{ ten }}</a>
                            </li>
                            {% set ten = (range_end + i * 10) - (range_end + i * 10) % 10 %}
                        {% endif %}
                    {% endfor %}
                    
                    {% if (number_page - ten) > 100 %}
                        {% set hundred = (ten + 100) - (ten + 100) % 100 %}
                        {% for i in 2..4 %}
                            {% if hundred < number_page %}
                                <li class="page-item">
                                    <a class="page-link" href="<?= get_href($route_name, $config, 'hundred') ?>">{{ hundred }}</a>
                                </li>
                                {% set hundred = (ten + i * 100) - (ten + i * 100) % 100 %}
                            {% endif %}
                        {% endfor %}
                    {% endif %}
                {% endif %}
                
                {% if number_page > range_end %}
                    {% if number_page > (range_end + 1) %}
                        {% if number_page > (range_end + 2) %}
                            <li class="page-item disabled">
                                <span class="page-link">&hellip;</span>
                            </li>
                        {% else %}
                            <li class="page-item">
                                <a class="page-link" href="<?= get_href($route_name, $config, 'number_page - 1') ?>">{{ number_page - 1 }}</a>
                            </li>
                        {% endif %}
                    {% endif %}
                    <li class="page-item">
                        <a class="page-link" href="<?= get_href($route_name, $config, 'number_page') ?>">{{ number_page }}</a>
                    </li>
                {% endif %}
                {% if next is defined %}
                    <li class="page-item">
                        <a class="page-link" rel="next" href="<?= get_href($route_name, $config, 'next') ?>">{{ 'button.next'|trans() }}&nbsp;&raquo;</a>
                    </li>
                {% else %}
                    <li  class="page-item disabled">
                        <span class="page-link">{{ 'button.next'|trans() }}&nbsp;&raquo;</span>
                    </li>
                {% endif %}
            </ul>
        </nav>
    </div>
{% endif %}