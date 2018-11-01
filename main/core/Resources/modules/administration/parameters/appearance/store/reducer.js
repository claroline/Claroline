import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {reducer as themeReducer} from '#/main/core/administration/parameters/appearance/components/theme/reducer'

const reducer = {
  parameters: makeFormReducer('parameters'),
  themes: themeReducer.themes
}

export {
  reducer
}
