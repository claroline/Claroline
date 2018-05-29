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
  const reducer = {
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

Ajouter des property par défaut dans un form:
----------------------------------------------

```
actions.open = (formName, id = null, defaultValue) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_role_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultValue, true))
  }
}
```

```
const UserTab = connect(
  ...
  dispatch => ({
    openForm(id = null, workspace, restrictions, collaboratorRole) {

      const defaultValue = {
        organization: null, //retreive it with axel stuff
        roles: [collaboratorRole]
      }

      dispatch(actions.open('users.current', id, defaultValue))
    }
  })
)(UserTabComponent)
```
