import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {UiMain as UiMainComponent} from '#/main/theme/administration/appearance/ui/components/main'
import {selectors} from '#/main/core/administration/parameters/store/selectors' // TODO : move

const UiMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    lockedParameters: selectors.lockedParameters(state),
    iconSetChoices: selectors.iconSetChoices(state)
  })
)(UiMainComponent)

export {
  UiMain
}
