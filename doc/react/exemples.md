FAQ:

Créer une modale avec la sélection d'une liste:
----------------------------------------------

```
const roleReducer = combineReducers({
  ...
  picker: makeListReducer(
    'roles.picker',
    {filters: [{property: 'type', value: PLATFORM_ROLE}]},
    {},
    {filterable: false, paginated: false}
  ),
  ...
})
```

```
  const reducer = makePageReducer({}, {
    ...
    roles: rolesReducer
    ...
  })
```

```
  dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
    icon: 'fa fa-fw fa-buildings',
    title: trans('add_roles'),
    confirmText: trans('add'),
    name: 'roles.picker',
    definition: RoleList.definition,
    card: RoleList.card,
    fetch: {
      url: generateUrl('apiv2_role_list'),
      autoload: true
    },
    handleSelect: (selectedRoles) => {
      alert('done')
    }
  }
```
