
Basic form
============

Render a form
-------------------------

```
    this.formField = {
      fields: [
        [
          'name',
          'text',
          {validators: [new NotBlank()]}
        ],
        [
          'type',
          'select',
          {
            values: [
              // these values currently come from the Entity/Facet/FieldFacet class
              { value: 1, label: 'text'},
              { value: 2, label: 'number'},
              { value: 3, label: 'date'},
              { value: 4, label: 'radio'},
              { value: 5, label: 'select'},
              { value: 6, label: 'checkbox'},
              { value: 7, label: 'country'}
            ]
          }
        ],
        ['is_visible_by_owner', 'checkbox', { label: 'visible'}],
        ['is_editable_by_owner', 'checkbox', { label: 'editable'}]
      ]
    }
```

```
    <form class="form-horizontal" name="myFormCtrl" ng-submit="onSubmit(myFormCtrl)">
        <formbuilder form="myForm" ng-model="myModel" form-ctrl="myFormCtrl"></formbuilder>
        <input data-ng-disabled="!myFormCtrl.$valid" type="submit"> </input>
    </form>
```


```
    const data = this.FormBuilderService.submit(url, parameters)

    this.$http.post(
      Routing.generate('this_is_a_route'),
      data,
      {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
    ).then(
      d => {
        //it worked !!
      },
      d => {
        //it failed !!
      }
    )
```

Available validators
----------
- not-blank

More specific use
=================

There is a directive for handling checkboxes array (no form field yet)

```
    <tr ng-repeat="(key, platformRole) in platformRoles track by platformRole.id">
        <td> {{ platformRole.translation_key|trans:{}:'platform' }} </td>
        <td> <input type="checkbox" checklist-model="roles" checklist-value="platformRole"> </input> </td>
    </tr>
```

- checklist-value is the value of the input
- checklist-model is the model array
