import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {UserCreate as UserCreateComponent} from '#/main/community/tools/community/user/components/create'

const UserCreate = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(UserCreateComponent)

export {
  UserCreate
}
