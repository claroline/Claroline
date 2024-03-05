import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {MenuBrand as MenuBrandComponent} from '#/main/app/layout/menu/components/brand'

const MenuBrand = connect(
  (state) => ({
    showTitle: true, //selectors.display(state).name,

    // platform parameters
    logo: configSelectors.param(state, 'logo'),
    title: configSelectors.param(state, 'name'),
    subtitle: configSelectors.param(state, 'secondaryName'),
  })
)(MenuBrandComponent)

export {
  MenuBrand
}
