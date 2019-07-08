import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {Workspace} from '#/main/core/workspace/prop-types'
import {reducer as creationReducer} from '#/main/core/workspace/creation/store'
const reducer = {
  workspaces: combineReducers({
    creation: creationReducer,
    list: makeListReducer('workspaces.list', {
      filters: [
        {property: 'meta.personal', value: false},
        {property: 'meta.model', value: false}
      ],
      sortBy: {property: 'created', direction: -1}
    }, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/workspaces.current']: () => true
      })
    }),
    current: makeFormReducer('workspaces.current', {data: Workspace.defaultProps, originalData: Workspace.defaultProps}, {
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
  parameters: makeFormReducer('parameters'),
  tools: makeReducer([]),
  models: makeReducer({})
}

export {
  reducer
}
