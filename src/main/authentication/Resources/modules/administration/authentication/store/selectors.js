
const STORE_NAME = 'authentication'

const store = (baseStore) => baseStore[STORE_NAME]

export const selectors = {
  STORE_NAME,
  store
}


