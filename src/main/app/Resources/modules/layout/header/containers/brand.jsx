import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as layoutSelectors} from '#/main/app/layout/store'

import {actions, selectors, reducer} from '#/main/app/layout/header/store'
import {HeaderBrand as HeaderBrandComponent} from '#/main/app/layout/header/components/brand'
import {actions as menuActions} from '#/main/app/layout/menu/store'

const HeaderBrand = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        showTitle: selectors.display(state).name,

        // platform parameters
        logo: configSelectors.param(state, 'logo'),
        title: configSelectors.param(state, 'name'),
        subtitle: configSelectors.param(state, 'secondaryName'),

        currentUser: securitySelectors.currentUser(state)
      }),
      (dispatch) => ({
        toggleMenu() {
          dispatch(menuActions.toggle())
        }
      })
    )(HeaderBrandComponent)
  )
)

export {
  HeaderBrand
}
