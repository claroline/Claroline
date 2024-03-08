import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {actions as configActions} from '#/main/app/config/store'

import {AppearanceMain as AppearanceMainComponent} from '#/main/theme/account/appearance/components/main'
import {selectors, reducer} from '#/main/theme/account/appearance/store'

const AppearanceMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      originalData: formSelectors.originalData(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      resetConfig(data) {
        dispatch(configActions.updateConfig('theme', data))
      },
      updateConfig(configKey, configValue) {
        dispatch(configActions.updateConfig('theme.'+configKey, configValue))
      }
    })
  )(AppearanceMainComponent)
)

export {
  AppearanceMain
}
