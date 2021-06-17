const STORE_NAME = 'logs'
const LIST_NAME = STORE_NAME + '.security'
const MESSAGE_NAME = STORE_NAME + '.message'
const FUNCTIONAL_NAME = STORE_NAME + '.functional'

const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  MESSAGE_NAME,
  FUNCTIONAL_NAME,

  store
}
