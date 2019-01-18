import {connect} from 'react-redux'

import {actions as walkthroughActions} from '#/main/app/overlay/walkthrough/store'

import {selectors} from '#/main/app/overlay/header/store'
import {Header as HeaderComponent} from '#/main/app/overlay/header/components/header'

const Header = connect(
  (state) => ({
    mainMenu: selectors.mainMenu(state),
    currentContext: selectors.context(state),
    logo: selectors.logo(state),
    title: selectors.title(state),
    subtitle: selectors.subtitle(state),
    display: selectors.display(state),
    count: selectors.count(state),
    helpUrl: selectors.helpUrl(state),
    loginUrl: selectors.loginUrl(state),
    registrationUrl: selectors.registrationUrl(state),
    currentUser: selectors.user(state),
    authenticated: selectors.authenticated(state),
    locale: selectors.locale(state),
    administration: selectors.administration(state),
    tools: selectors.tools(state),
    userTools: selectors.userTools(state),
    notificationTools: selectors.notificationTools(state),
    maintenance: selectors.maintenance(state),
    redirectHome: selectors.redirectHome(state)
  }),
  (dispatch) => ({
    startWalkthrough(steps, additional, documentation) {
      dispatch(walkthroughActions.start(steps, additional, documentation))
    }
  })
)(HeaderComponent)

export {
  Header
}
