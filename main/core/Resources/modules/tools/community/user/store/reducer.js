import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/tools/community/store/selectors'

const reducer = combineReducers({
  picker: makeListReducer(selectors.STORE_NAME + '.users.picker'),
  list: makeListReducer(selectors.STORE_NAME + '.users.list', {
    sortBy: {property: 'created', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.users.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.users.current', {}, {
    workspaces: makeListReducer(selectors.STORE_NAME + '.users.current.workspaces'),
    groups: makeListReducer(selectors.STORE_NAME + '.users.current.groups'),
    organizations: makeListReducer(selectors.STORE_NAME + '.users.current.organizations'),
    roles: makeListReducer(selectors.STORE_NAME + '.users.current.roles')
  })
})

export {
  reducer
}
