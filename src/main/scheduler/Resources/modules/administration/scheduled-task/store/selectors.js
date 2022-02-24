const STORE_NAME = 'scheduled_tasks'

const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  store
}
