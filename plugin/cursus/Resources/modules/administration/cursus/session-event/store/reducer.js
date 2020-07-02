import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {LIST_DATA_DELETE} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.events.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.events.current']: () => true,
      [LIST_DATA_DELETE + '/' + selectors.STORE_NAME + '.sessions.current.events']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.events.current', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.events.current.users')
  })
})

export {
  reducer
}
