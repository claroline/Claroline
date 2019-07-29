import get from 'lodash/get'

const STORE_NAME = 'config'

const store = (state) => state[STORE_NAME]

const param = (state, paramPath) => get(store(state), paramPath)

export const selectors = {
  STORE_NAME,

  store,
  param
}
