import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/theme/administration/appearance/store'

import {AppearanceParameters as AppearanceParametersComponent} from '#/main/theme/administration/appearance/components/parameters'

const AppearanceParameters = connect(
  (state) => ({
    path: toolSelectors.path(state),
    availableThemes: selectors.availableThemes(state),
    availableIconSets: selectors.availableIconSets(state)
  }),
  (dispatch) => ({
    addIconSet(iconSet) {
      dispatch(actions.addIconSet(iconSet))
    },
    removeIconSet(iconSet) {
      dispatch(actions.removeIconSet(iconSet))
    },
    addColorChart(colorChart) {
      dispatch(actions.addColorChart(colorChart))
    },
    updateColorChart(colorChart) {
      dispatch(actions.updateColorChart(colorChart))
    },
    removeColorChart(colorChart) {
      dispatch(actions.removeColorChart(colorChart))
    }
  })
)(AppearanceParametersComponent)

export {
  AppearanceParameters
}
