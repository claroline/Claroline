import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const USER_LOAD_EXTERNAL_ACCOUNTS = 'USER_LOAD_EXTERNAL_ACCOUNTS'

export const actions = {}

actions.loadAccounts = makeActionCreator(USER_LOAD_EXTERNAL_ACCOUNTS, 'data')

actions.fetchAccounts = () => ({
  [API_REQUEST]: {
    url: [],
    success: (response, dispatch) => dispatch(actions.loadAccounts(response))
  }
})
