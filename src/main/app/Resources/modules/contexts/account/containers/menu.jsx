import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {AccountMenu as AccountMenuComponent} from '#/main/app/contexts/account/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const AccountMenu = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    roles: contextSelectors.roles(state),
    basePath: contextSelectors.path(state),
    tools: contextSelectors.tools(state),
    shortcuts: contextSelectors.shortcuts(state)
  })
)(AccountMenuComponent)

export {
  AccountMenu
}
