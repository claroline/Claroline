
const FORM_NAME = 'resourceRights.form'
const STORE_NAME = 'resourceRights'

const recursiveEnabled = (state) => {
  return state[STORE_NAME].recursiveEnabled
}

export const selectors = {
  FORM_NAME,
  STORE_NAME,
  recursiveEnabled
}
