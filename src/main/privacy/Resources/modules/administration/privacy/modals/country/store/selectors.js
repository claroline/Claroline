const STORE_NAME = 'privacy'

// state selectors
const store = (state) => state[STORE_NAME]
const data = (state) => store(state).data
const originalData = (state) => store(state).originalData

export const selectors = {
  STORE_NAME,
  store,
  data,
  originalData
}
