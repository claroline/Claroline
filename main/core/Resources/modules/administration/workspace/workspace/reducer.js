import {combineReducers} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

const reducer = makePageReducer({}, {
  workspaces: combineReducers({
    picker: makeListReducer('workspaces.picker'),
    list: makeListReducer('workspaces.list', {
      filters: [
        {property: 'meta.personal', value: false},
        {property: 'meta.model', value: false}
      ]
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
  })
})

export {
  reducer
}
