import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserForm} from '#/main/community/user/components/form'
import {selectors} from '#/main/community/tools/community/user/store'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {ContentSizing} from '#/main/app/content/components/sizing'

const UserCreate = (props) =>
  <ToolPage
    size="xl"
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('users', {}, 'community'),
        target: `${props.path}/users`
      }
    ]}
    icon={
      <UserAvatar className="img-thumbnail" noStatus={true} size="xl" />
    }
    title={trans('new_user', {}, 'community')}
  >
    <ContentSizing size="lg">
      <UserForm
        path={`${props.path}/users/new`}
        back={`${props.path}/users`}
        name={selectors.FORM_NAME}
      />
    </ContentSizing>
  </ToolPage>

UserCreate.propTypes = {
  path: T.string
}

export {
  UserCreate
}
