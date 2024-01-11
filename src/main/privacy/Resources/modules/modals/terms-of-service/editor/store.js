import {makeFormReducer} from '#/main/app/content/form/store'
import {API_REQUEST} from '#/main/app/api'

const STORE_NAME = 'tosEditor'

const store = (state) => state[STORE_NAME]

const selectors = {
  STORE_NAME,
  store
}

export const actions = {}

actions.saveForm = (data) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_privacy_update'],
    request: {
      method: 'PUT',
      body: JSON.stringify(data)
    }
  }
})

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer,
  selectors
}
