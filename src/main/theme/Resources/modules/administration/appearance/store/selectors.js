import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = 'appearance'

const FORM_NAME = STORE_NAME+'.parameters'
const THEME_NAME = STORE_NAME+'.currentTheme'

const store = (state) => state[STORE_NAME]

const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const availableThemes = createSelector(
  [store],
  (store) => store.availableThemes || []
)

const availableIconSets = createSelector(
  [store],
  (store) => store.availableIconSets
)

const availableColorCharts = createSelector(
  [store],
  (store) => store.availableColorCharts
)

const currentIconSet = createSelector(
  [parameters, availableIconSets],
  (parameters, availableIconSets) => {
    return null
    const currentSetName = get(parameters, 'icons')

    return availableIconSets.find(iconSet => iconSet.name === currentSetName)
  }
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  THEME_NAME,

  store,
  availableThemes,
  availableIconSets,
  currentIconSet,
  availableColorCharts
}
