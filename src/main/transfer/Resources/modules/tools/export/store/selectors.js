import get from 'lodash/get'
import {createSelector} from 'reselect'

const STORE_NAME = 'export'
const LIST_NAME = STORE_NAME+'.list'
const FORM_NAME = STORE_NAME+'.form'

const store = (state) => get(state, STORE_NAME)

const exportFile  = createSelector(
  [store],
  (store) => store.details
)

const exportExplanation = createSelector(
  [store],
  (store) => store.explanation
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,
  store,

  exportFile,
  exportExplanation
}
