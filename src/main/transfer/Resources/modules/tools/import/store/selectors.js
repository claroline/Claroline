import get from 'lodash/get'
import {createSelector} from 'reselect'

const STORE_NAME = 'import'
const LIST_NAME = STORE_NAME+'.list'
const FORM_NAME = STORE_NAME+'.form'


const store = (state) => get(state, STORE_NAME)

const importFile  = createSelector(
  [store],
  (store) => store.details
)

const importExplanation = createSelector(
  [store],
  (store) => store.explanation
)

const importSamples = createSelector(
  [store],
  (store) => store.samples
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,
  store,

  importFile,
  importExplanation,
  importSamples
}
