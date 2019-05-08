import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  apps: makeListReducer('lti.apps', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/lti.apps']: () => true
    })
  }),
  app: makeFormReducer('lti.app', {}, {})
})

export {
  reducer
}
