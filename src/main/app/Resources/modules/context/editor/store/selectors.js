import {createSelector} from 'reselect'
import {trans} from '#/main/app/intl'

const STORE_NAME = 'contextEditor'
const FORM_NAME = STORE_NAME+'.form'

const store = (state) => state[STORE_NAME]

const availableTools = createSelector(
  [store],
  (store) => [].concat(store.availableTools).sort((a, b) => {
    if (trans(a.name, {}, 'tools') > trans(b.name, {}, 'tools')) {
      return 1
    }

    return -1
  })
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  availableTools
}
