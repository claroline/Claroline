import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as paramSelectors} from '#/main/core/administration/parameters/store'
import {actions, selectors} from '#/main/theme/administration/appearance/store'

import {AppearanceTool as AppearanceToolComponent} from '#/main/theme/administration/appearance/components/tool'

const AppearanceTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: paramSelectors.lockedParameters(state),
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
)(AppearanceToolComponent)

export {
  AppearanceTool
}
