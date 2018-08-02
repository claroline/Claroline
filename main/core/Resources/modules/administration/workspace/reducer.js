import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {reducer as creationReducer} from '#/main/core/workspace/creation/store'

const reducer = {
  workspaces: combineReducers({
    creation: creationReducer,
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
    user: makeListReducer('selected.user'),
    group: makeListReducer('selected.group')
  }),
  parameters: makeFormReducer('parameters'),
  tools: makeReducer([], {}),
  models: makeReducer()
}

export {
  reducer
}
