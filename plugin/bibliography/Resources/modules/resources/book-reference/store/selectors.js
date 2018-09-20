const STORE_NAME = 'resource'

const FORM_NAME = `${STORE_NAME}.bookReference`

const resource = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  resource
}
