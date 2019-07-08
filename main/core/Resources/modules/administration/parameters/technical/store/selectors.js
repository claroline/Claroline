import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'

const STORE_NAME = 'technical_settings'
const FORM_NAME = STORE_NAME+'.parameters'

const store = (state) => state[STORE_NAME]

const toolChoices = createSelector(
  [store],
  (store) => store.tools.reduce((acc, current) => Object.assign(acc, {
    [current]: trans(current, {}, 'tools')
  }), {})
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  toolChoices
}
