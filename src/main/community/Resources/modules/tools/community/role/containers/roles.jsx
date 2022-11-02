import React from 'react'
import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {Roles as RolesComponent} from '#/main/community/tools/community/role/components/roles'

const Roles = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  })
)(RolesComponent)

export {
  Roles
}
