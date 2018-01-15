import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {getDataQueryString} from '#/main/core/data/list/utils'

import {API_REQUEST} from '#/main/core/api/actions'

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
  [API_REQUEST]: {
    url: ['apiv2_theme_update', {id: theme.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(theme)
    },
    success: (data, dispatch) => dispatch(actions.updateTheme(data))
  }
})

actions.deleteThemes = (themes) => {
  const themeIds = themes.map(theme => theme.id)

  return {
    [API_REQUEST]: {
      url: ['apiv2_theme_delete_bulk'] + getDataQueryString(themes),
      request: {
        method: 'DELETE'
      },
      success: (data, dispatch) => dispatch(actions.removeThemes(themeIds))
    }
  }
}
