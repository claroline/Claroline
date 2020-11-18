import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {actions as securityActions, selectors as securitySelectors} from '#/main/app/security/store'
import {actions as layoutActions, selectors as layoutSelectors} from '#/main/app/layout/store'

import {actions as toolActions} from '#/main/core/tool/store'

import {HomeMain as HomeMainComponent} from '#/main/app/layout/sections/home/components/main'
import {selectors} from '#/main/app/layout/sections/home/store'
import {constants} from '#/main/app/layout/sections/home/constants'

const HomeMain = connect(
  (state) => ({
    unavailable: layoutSelectors.unavailable(state),
    maintenance: layoutSelectors.maintenance(state),
    disabled: layoutSelectors.disabled(state),
    restrictions: configSelectors.param(state, 'restrictions'),
    maintenanceMessage: layoutSelectors.maintenanceMessage(state),
    hasHome: selectors.hasHome(state),
    homeType: selectors.homeType(state),
    homeData: selectors.homeData(state),
    authenticated: securitySelectors.isAuthenticated(state),
    selfRegistration: configSelectors.param(state, 'selfRegistration')
  }),
  (dispatch) => ({
    openHome(type, data) {
      if (constants.HOME_TYPE_URL === type) {
        window.location.href = data
      } else if (constants.HOME_TYPE_TOOL === type) {
        dispatch(toolActions.open('home', {
          type: 'home', // TODO : use var
          url: ['apiv2_home'],
          data: {}
        }, ''))
      }
    },
    linkExternalAccount(service, username, onSuccess) {
      return dispatch(securityActions.linkExternalAccount(service, username, onSuccess))
    },
    reactivate() {
      return dispatch(layoutActions.extend())
    }
  })
)(HomeMainComponent)

export {
  HomeMain
}
