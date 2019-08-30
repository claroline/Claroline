import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const STORE_NAME = 'header'

const store = (state) => state[STORE_NAME]

const menus = createSelector(
  [store],
  (store) => store.menus
)

const tools = createSelector(
  [store],
  (store) => store.tools
)

const display = createSelector(
  [store],
  (store) => store.display
)

// this will later be retrieved from the store
const logo = (state) => configSelectors.param(state, 'logo')
const title = (state) => configSelectors.param(state, 'name')
const subtitle = (state) => configSelectors.param(state, 'secondaryName')
const locale = (state) => configSelectors.param(state, 'locale')

const helpUrl = (state) => {
  const displayParams = display(state)
  if (displayParams.help) {
    return configSelectors.param(state, 'helpUrl')
  }

  return null
}

export const selectors = {
  STORE_NAME,

  menus,
  tools,
  logo,
  title,
  subtitle,
  display,
  locale,
  helpUrl
}
