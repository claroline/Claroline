import {connect} from 'react-redux'

import {AccountMenu as AccountMenuComponent} from '#/main/app/contexts/account/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const AccountMenu = connect(
  (state) => ({
    tools: contextSelectors.tools(state)
  })
)(AccountMenuComponent)

export {
  AccountMenu
}
