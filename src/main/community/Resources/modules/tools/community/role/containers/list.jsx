import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {RoleList as RoleListComponent} from '#/main/community/tools/community/role/components/list'
import {selectors} from '#/main/community/tools/community/role/store/selectors'

const RoleList = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    canCreate: selectors.canCreate(state)
  })
)(RoleListComponent)

export {
  RoleList
}
