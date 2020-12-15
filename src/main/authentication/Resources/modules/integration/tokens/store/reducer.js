import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  tokens: makeListReducer('api_tokens.tokens', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/api_tokens.token']: () => true
    })
  }),
  token: makeFormReducer('api_tokens.token', {}, {})
})

export {
  reducer
}
