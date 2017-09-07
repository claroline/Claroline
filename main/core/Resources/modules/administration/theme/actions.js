import {makeActionCreator} from '#/main/core/utilities/redux'

import {REQUEST_SEND} from '#/main/core/api/actions'

export const THEMES_REMOVE  = 'THEMES_REMOVE'
export const THEMES_REBUILD = 'THEMES_REBUILD'
export const THEME_UPDATE   = 'THEME_UPDATE'

export const actions = {}

actions.removeThemes = makeActionCreator(THEMES_REMOVE, 'themeIds')
actions.rebuildThemes = makeActionCreator(THEMES_REBUILD, 'themes')
actions.updateTheme = makeActionCreator(THEME_UPDATE, 'theme')

actions.saveTheme = (theme) => ({
  [REQUEST_SEND]: {
    route: ['claro_theme_update', {id: theme.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(theme)
    },
    success: (data) => actions.updateTheme(data)
  }
})

actions.deleteThemes = (themes) => {
  const themeIds = themes.map(theme => theme.id)

  return {
    [REQUEST_SEND]: {
      route: ['claro_themes_delete'],
      request: {
        method: 'DELETE',
        body: JSON.stringify(themeIds)
      },
      success: (data) => actions.removeThemes(themeIds)
    }
  }
}