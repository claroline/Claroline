import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {HomeMenu as HomeMenuComponent} from '#/main/app/layout/sections/home/components/menu'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'

const HomeMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    registration: configSelectors.param(state, 'selfRegistration'),
    section: menuSelectors.openedSection(state)
  }),
  (dispatch) => ({
    changeSection(section) {
      dispatch(menuActions.changeSection(section))
    }
  })
)(HomeMenuComponent)

export {
  HomeMenu
}
