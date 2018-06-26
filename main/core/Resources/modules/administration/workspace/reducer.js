import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

const reducer = {
  workspaces: combineReducers({
    picker: makeListReducer('workspaces.picker'),
    list: makeListReducer('workspaces.list', {
      filters: [
        {property: 'meta.personal', value: false},
        {property: 'meta.model', value: false}
      ]
    }, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/workspaces.current']: () => true
      })
    }),
    current: makeFormReducer('workspaces.current', {}, {
      organizations: makeListReducer('workspaces.current.organizations'),
      managers: makeListReducer('workspaces.current.managers')
    })
  }),
  organizations: combineReducers({
    picker: makeListReducer('organizations.picker')
  }),
  managers: combineReducers({
    picker: makeListReducer('managers.picker')
  }),
  selected: combineReducers({
    user: makeListReducer(
      'selected.user'
    ),
    group: makeListReducer(
      'selected.group'
    )
  }),
  parameters: makeFormReducer('parameters')
}

export {
  reducer
}
