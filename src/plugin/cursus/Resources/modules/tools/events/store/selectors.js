const STORE_NAME = 'training_events'
const LIST_NAME = STORE_NAME+'.list'

const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  store
}