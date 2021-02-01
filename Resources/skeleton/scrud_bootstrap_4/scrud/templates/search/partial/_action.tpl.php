                        <td>
<?php if ($config['read']['activate']): ?>
                            {% if can_read %}
                                <a href="{{ path('<?= $route_name ?>_read', {'<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}" title="{{ 'button.read_title'|trans() }}"
                                    class="btn btn-primary" aria-label="{{ 'button.read_title'|trans() }}" role="button">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                            {% endif %}
<?php endif ?>
<?php if ($config['update']['activate']): ?>
                            {% if can_update %}
<?php if ($config['update']['multi_select']): ?>
                                <a href="{{ path('<?= $route_name ?>_update', {'<?= $entity_identifier_snake_case_plural ?>': {0: <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}}) }}" title="{{ 'button.update_title'|trans() }}"
<?php else: ?>
                                <a href="{{ path('<?= $route_name ?>_update', {'<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}" title="{{ 'button.update_title'|trans() }}"
<?php endif ?>
                                    class="btn btn-warning" aria-label="{{ 'button.update_title'|trans() }}" role="button">
                                    <i class="fas fa-edit"></i>
                                </a>
                            {% endif %}
<?php endif ?>
<?php if ($config['delete']['activate']): ?>
                            {% if can_delete %}
                                <a href="#" class="btn btn-danger btn-delete" data-toggle="modal" data-target="#delete"
                                    data-title="{{ <?= $entity_snake_case ?> }}" role="button"
<?php if ($config['delete']['multi_select']): ?>
                                    data-path="{{ path('<?= $route_name ?>_delete', { '<?= $entity_identifier_snake_case_plural ?>': {0: <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}}) }}"
<?php else: ?>
                                    data-path="{{ path('<?= $route_name ?>_delete', { '<?= $entity_identifier_snake_case ?>': <?= $entity_snake_case ?>.<?= $entity_identifier_lower_camel_case ?>}) }}"
<?php endif ?>
                                    title="{{ 'button.delete_title'|trans() }}" aria-label="{{ 'button.delete_title'|trans() }}">
                                    <i class="fas fa-times"></i>
                                </a>
                            {% endif %}
<?php endif ?>
                        </td>