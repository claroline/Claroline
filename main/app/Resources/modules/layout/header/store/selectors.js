import {createSelector} from 'reselect'

import {param} from '#/main/app/config'

const STORE_NAME = 'header'

const store = (state) => state[STORE_NAME]

const menus = createSelector(
  [store],
  (store) => store.menus
)
const administration = createSelector(
  [store],
  (store) => store.administration
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
const logo = () => param('logo')
const title = () => param('name')
const subtitle = () => param('secondaryName')
const locale = () => param('locale')

const helpUrl = createSelector(
  [display],
  (display) => {
    if (display.help) {
      return param('helpUrl')
    }

    return null
  }
)

export const selectors = {
  STORE_NAME,

  menus,
  administration,
  tools,
  logo,
  title,
  subtitle,
  display,
  locale,
  helpUrl
}
