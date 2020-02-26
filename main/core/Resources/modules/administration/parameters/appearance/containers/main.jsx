import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {AppearanceMain as AppearanceMainComponent} from '#/main/core/administration/parameters/appearance/components/main'

const AppearanceMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    iconSetChoices: selectors.iconSetChoices(state)
  })
)(AppearanceMainComponent)

export {
  AppearanceMain
}
