const STORE_NAME = 'training_events'

const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  store
}