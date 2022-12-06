import React from 'react'
import {PropTypes as T} from 'prop-types'

import {User as UserTypes} from '#/main/community/user/prop-types'
import {UserPage} from '#/main/community/user/components/page'

const UserShow = (props) =>
  <UserPage
    path={props.path}
    user={props.user}
    reload={props.reload}
  >

  </UserPage>

UserShow.propTypes = {
  path: T.string.isRequired,
  user: T.shape(
    UserTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  UserShow
}
