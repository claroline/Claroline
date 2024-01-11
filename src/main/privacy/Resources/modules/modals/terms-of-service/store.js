import {createSelector} from 'reselect'
import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const STORE_NAME = 'termsOfService'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const content = createSelector(
  [store],
  (store) => store.content
)

const selectors = {
  STORE_NAME,
  loaded,
  content
}

export const TERMS_OF_SERVICE_LOAD = 'TERMS_OF_SERVICE_LOAD'

export const actions = {}

actions.load = makeActionCreator(TERMS_OF_SERVICE_LOAD, 'content')

actions.fetch = () => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_platform_terms_of_service'],
    silent: true,
    success: (response) => dispatch(actions.load(response))
  }
})

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [TERMS_OF_SERVICE_LOAD]: () => true
  }),
  content: makeReducer(null, {
    [TERMS_OF_SERVICE_LOAD]: (state, action) => action.content
  })
})

export {
  selectors
}
