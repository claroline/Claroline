import {createSelector} from 'reselect'



const STORE_NAME = 'tinymceUpload'
const FORM_NAME = `${STORE_NAME}.form`

const store = (state) => state[STORE_NAME]

const uploadDestinations = createSelector(
  [store],
  (store) => store.uploadDestinations
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  uploadDestinations
}
