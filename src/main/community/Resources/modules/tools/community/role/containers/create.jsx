import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {RoleCreate as RoleCreateComponent} from '#/main/community/tools/community/role/components/create'

const RoleCreate = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(RoleCreateComponent)

export {
  RoleCreate
}
