const STORE_NAME = 'logs'
const LIST_NAME = STORE_NAME + '.securityLogs'
const MESSAGE_NAME = STORE_NAME + '.messageLogs'
const FUNCTIONAL_NAME = STORE_NAME + '.functionalLogs'

const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  MESSAGE_NAME,
  FUNCTIONAL_NAME,

  store
}
