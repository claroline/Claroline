const STORE_NAME = 'example'
const store = (state) => state[STORE_NAME]

const FORM_NAME = STORE_NAME+'.form'

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  store
}
