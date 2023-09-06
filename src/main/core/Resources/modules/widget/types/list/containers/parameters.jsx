import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {ListWidgetParameters as ListWidgetParametersComponent} from '#/main/core/widget/types/list/components/parameters'

const ListWidgetParameters = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(ListWidgetParametersComponent)

export {
  ListWidgetParameters
}
