import isEmpty from 'lodash/isEmpty'
import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store'

const STORE_NAME = 'administration'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const tools = createSelector(
  [store],
  (store) => store.tools
)

const defaultOpening = (state) => {
  const adminTools = tools(state)
  if (!isEmpty(adminTools)) {
    let defaultTool = configSelectors.param(state, 'admin.defaultTool')

    if (!defaultTool || -1 === adminTools.findIndex(tool => defaultTool === tool.name)) {
      // open the first available tool
      defaultTool = adminTools[0].name
    }

    return defaultTool
  }

  return null
}

export const selectors = {
  STORE_NAME,

  loaded,
  tools,
  defaultOpening
}