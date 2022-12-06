import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserForm} from '#/main/community/user/components/form'
import {selectors} from '#/main/community/tools/community/user/store'

const UserCreate = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('users', {}, 'community'),
        target: `${props.path}/users`
      }, {
        type: LINK_BUTTON,
        label: trans('new_user', {}, 'community'),
        target: '' // current page, no need to add a link
      }
    ]}
    primaryAction="add"
    subtitle={trans('new_user', {}, 'community')}
    actions={[{
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_user'),
      target: `${props.path}/users/new`,
      primary: true
    }]}
  >
    <UserForm
      path={`${props.path}/users/new`}
      back={`${props.path}/users`}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

UserCreate.propTypes = {
  path: T.string
}

export {
  UserCreate
}
