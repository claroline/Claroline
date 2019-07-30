import get from 'lodash/get'

const STORE_NAME = 'config'

const config = (state) => state[STORE_NAME]

const param = (state, paramPath) => get(config(state), paramPath)

export const selectors = {
  STORE_NAME,

  config,
  param
}
