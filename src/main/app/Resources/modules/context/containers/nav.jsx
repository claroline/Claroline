import {connect} from 'react-redux'

import {ContextNav as ContextNavComponent} from '#/main/app/context/components/nav'
import {selectors} from '#/main/app/context/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as layoutSelectors} from '#/main/app/layout/store'

const ContextNav = connect(
  (state) => ({
    currentContext: selectors.data(state),
    currentContextType: selectors.type(state),
    currentContextPath: selectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    availableContexts: layoutSelectors.availableContexts(state),
    favoriteContexts: layoutSelectors.favoriteContexts(state)
  })
)(ContextNavComponent)

export {
  ContextNav
}
