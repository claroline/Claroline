import {makeActionCreator} from '#/main/core/utilities/redux'

import {REQUEST_SEND} from '#/main/core/api/actions'

export const THEMES_REMOVE    = 'THEMES_REMOVE'
export const THEMES_REBUILD   = 'THEMES_REBUILD'
export const THEME_UPDATE     = 'THEME_UPDATE'
export const THEME_EDIT       = 'THEME_EDIT'
export const THEME_FORM_RESET = 'THEME_FORM_RESET'

export const actions = {}

actions.removeThemes = makeActionCreator(THEMES_REMOVE, 'themeIds')
actions.rebuildThemes = makeActionCreator(THEMES_REBUILD, 'themes')
actions.updateTheme = makeActionCreator(THEME_UPDATE, 'theme')
actions.editTheme = makeActionCreator(THEME_EDIT, 'theme')
actions.resetThemeForm = makeActionCreator(THEME_FORM_RESET)

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
      success: () => actions.removeThemes(themeIds)
    }
  }
}