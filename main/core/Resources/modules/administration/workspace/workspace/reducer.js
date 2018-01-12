import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {FORM_RESET} from '#/main/core/data/form/actions'

const workspacesReducer = combineReducers({
  picker: makeListReducer('workspaces.picker'),
  list: makeListReducer('workspaces.list'),
  current: makeFormReducer('workspaces.current', {}, {
    organizations: makeListReducer('workspaces.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/workspaces.current']: () => true // todo : find better
      })
    }),
    managers: makeListReducer('workspaces.current.managers', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/workspaces.current']: () => true // todo : find better
      })
    })
  })
})

const organizationReducer = combineReducers({
  picker: makeListReducer('organizations.picker')
})

const managersReducer = combineReducers({
  picker: makeListReducer('managers.picker')
})

const reducer = makePageReducer({}, {
  workspaces: workspacesReducer,
  organizations: organizationReducer,
  managers: managersReducer
})

export {
  reducer
}
