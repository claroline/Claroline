import get from 'lodash/get'

const STORE_NAME = 'config'

const config = (state) => state[STORE_NAME]

const param = (state, paramPath, paramDefault) => get(config(state), paramPath, paramDefault)

export const selectors = {
  STORE_NAME,

  config,
  param
}
