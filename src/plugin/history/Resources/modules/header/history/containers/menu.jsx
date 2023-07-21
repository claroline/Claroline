import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {HistoryMenu as HistoryMenuComponent} from '#/plugin/history/header/history/components/menu'

const HistoryMenu = connect(
  (state) => ({
    isAuthenticated: securitySelectors.isAuthenticated(state)
  })
)(HistoryMenuComponent)

export {
  HistoryMenu
}
