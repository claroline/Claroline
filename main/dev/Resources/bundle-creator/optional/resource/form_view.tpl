<div class='modal-dialog'>
    <form role="form" novalidate="novalidate"
          action="{{ path('claro_resource_create', {'resourceType':resourceType, 'parentId':'_nodeId'}) }}"
          method="post" {{ form_enctype(form) }}
          id="generic-form"
          novalidate
          class="modal-content">
        <div class='modal-header'>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{ 'create_resource' | trans({}, 'platform') }} ({{ resourceType | trans({}, 'resource') }})</h4>
        </div>
        <div class='modal-body'>
            {{ form_widget(form) }}
            {{ form_rest(form) }}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ 'cancel'|trans({}, 'platform') }}</button>
            <button type="submit" class="btn btn-primary">{{ 'ok'|trans({}, 'platform') }}</button>
        </div>
    </form>
</div>
