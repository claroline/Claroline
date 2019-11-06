import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {Appearance as AppearanceComponent} from '#/main/core/administration/parameters/appearance/components/appearance'

const Appearance = connect(
  (state) => ({
    path: toolSelectors.path(state),
    iconSetChoices: selectors.iconSetChoices(state)
  })
)(AppearanceComponent)

export {
  Appearance
}
