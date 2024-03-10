
const STORE_NAME = 'authentication'
const FORM_NAME = STORE_NAME+'.form'

const store = (baseStore) => baseStore[STORE_NAME]

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  store
}


