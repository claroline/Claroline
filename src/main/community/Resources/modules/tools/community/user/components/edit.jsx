import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {ContentSizing} from '#/main/app/content/components/sizing'

import {UserPage} from '#/main/community/user/components/page'
import {User as UserTypes} from '#/main/community/user/prop-types'
import {selectors} from '#/main/community/tools/community/user/store'
import {UserForm} from '#/main/community/user/components/form'

const UserEdit = (props) =>
  <UserPage
    path={props.path}
    user={props.user}
    reload={props.reload}
  >
    {!isEmpty(props.user) &&
      <ContentSizing size="lg">
        <UserForm
          name={selectors.FORM_NAME}
          path={`${props.path}/users/${props.username}/edit`}
          back={`${props.path}/users/${props.username}`}
        />
      </ContentSizing>
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
