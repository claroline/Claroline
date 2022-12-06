import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserPage} from '#/main/community/user/components/page'
import {User as UserTypes} from '#/main/community/user/prop-types'
import {selectors} from '#/main/community/tools/community/user/store'
import {UserForm} from '#/main/community/user/components/form'
import isEmpty from 'lodash/isEmpty'

const UserEdit = (props) =>
  <UserPage
    path={props.path}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('edition'),
        target: '' // current page, link is not needed
      }
    ]}
    user={props.user}
    reload={props.reload}
  >
    {!isEmpty(props.user) &&
      <UserForm
        name={selectors.FORM_NAME}
        path={`${props.path}/users/${props.username}/edit`}
        back={`${props.path}/users/${props.username}`}
      />
    }
  </UserPage>

UserEdit.propTypes = {
  path: T.string.isRequired,
  username: T.string,
  user: T.shape(
    UserTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  UserEdit
}
