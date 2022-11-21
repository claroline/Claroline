import {connect} from 'react-redux'

import {AppearanceIcons as AppearanceIconsComponent} from '#/main/theme/administration/appearance/components/icons'
import {selectors} from '#/main/theme/administration/appearance/store'

const AppearanceIcons = connect(
  (state) => ({
    currentIconSet: selectors.currentIconSet(state)
  })
)(AppearanceIconsComponent)

export {
  AppearanceIcons
}
