import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {AppBrand as AppBrandComponent} from '#/main/app/layout/components/brand'

const AppBrand = connect(
  (state) => ({
    showTitle: true, //selectors.display(state).name,

    // platform parameters
    logo: configSelectors.param(state, 'logo'),
    title: configSelectors.param(state, 'name'),
    subtitle: configSelectors.param(state, 'secondaryName'),
  })
)(AppBrandComponent)

export {
  AppBrand
}
