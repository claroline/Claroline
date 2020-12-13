import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {reducer, selectors} from '#/main/theme/appearance/store'
import {AppearanceMain as AppearanceMainComponent} from '#/main/theme/appearance/components/main'

const AppearanceMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(

  )(AppearanceMainComponent)
)

export {
  AppearanceMain
}
