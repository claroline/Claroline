import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {AppBrand as AppBrandComponent} from '#/main/app/layout/components/brand'

const AppBrand = connect(
  (state) => ({
    name: configSelectors.param(state, 'name'),
    logo: configSelectors.param(state, 'logo')
  })
)(AppBrandComponent)

export {
  AppBrand
}
