Modal
=====

Simple modal management build on top of AngularBootstrap

Add modals to your module dependencies
--------------------------------------

```
angular
    .module('MY_MODULE', [
        'ui.modal'
    ])
```

Directives usage
----------------

**Confirm**

```
<button type="button"
    data-confirm-modal="{{ 'confirm_message'|trans:{}:'domain'}}"
    data-confirm-modal-action="confirmCallback()"
    data-confirm-modal-cancel="cancelCallback()"
>
    My toggle modal button
</button>
```

Where :
- `data-confirm-modal` : the message to display in the modal
- `data-confirm-modal-action` : the callback to execute if the User confirm his action (can be a method of the parent controller)
- `data-confirm-modal-cancel` : the callback to execute if the User cancel his action (can be a method of the parent controller)