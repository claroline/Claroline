import {connect} from 'react-redux'

import {selectors} from '#/main/app/overlay/header/store'
import {Header as HeaderComponent} from '#/main/app/overlay/header/components/header'

const Header = connect(
  (state) => ({
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
    workspaces: selectors.workspaces(state),
    locale: selectors.locale(state),
    administration: selectors.administration(state),
    tools: selectors.tools(state),
    userTools: selectors.userTools(state),
    maintenance: selectors.maintenance(state),
    currentLocation: selectors.current(state)
  })
)(HeaderComponent)

export {
  Header
}
