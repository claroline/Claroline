const STORE_NAME = 'privacyDpo'
const store = (state) => state[STORE_NAME]
const FORM_NAME = STORE_NAME+'.parameters'
export const selectors = {
  STORE_NAME,
  store,
  FORM_NAME
}