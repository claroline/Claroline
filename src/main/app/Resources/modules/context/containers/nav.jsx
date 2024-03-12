import {connect} from 'react-redux'

import {ContextNav as ContextNavComponent} from '#/main/app/context/components/nav'
import {selectors} from '#/main/app/context/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

const ContextNav = connect(
  (state) => ({
    currentContext: selectors.data(state),
    currentContextType: selectors.type(state),
    currentContextPath: selectors.path(state),
    currentUser: securitySelectors.currentUser(state)
  })
)(ContextNavComponent)

export {
  ContextNav
}
