import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ProfileShow} from '#/main/community/profile/containers/show'
import {ProfileEdit} from '#/main/community/profile/containers/edit'

import {selectors} from '#/main/app/context/profile/store'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {ContextPage} from '#/main/app/context/components/page'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {PageHeading} from '#/main/app/page/components/heading'

const ContextProfile = (props) => {
  return (
    <ContextPage
      size="xl"
      title={trans('my_profile')}
      poster={props.currentUser.poster}
      breadcrumb={[
        {
          type: LINK_BUTTON,
          label: trans('my_profile'),
          target: `${props.path}/profile`
        }
      ]}
    >
      <PageHeading
        size="md"
        title={props.currentUser.name}
        icon={<UserAvatar user={props.currentUser} size="xl" />}
        primaryAction="edit"
        actions={[
          {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: `${props.path}/profile/edit`,
            primary: true
          }
        ]}
      />

      <ContentSizing size="lg">
        <Routes
          path={`${props.path}/profile`}
          routes={[
            {
              path: '/edit',
              disabled: !hasPermission('edit', props.currentUser),
              onEnter: () => props.reset(props.currentUser),
              render: () => (
                <ProfileEdit
                  name={selectors.STORE_NAME}
                  path={`${props.path}/profile/edit`}
                  user={props.currentUser}
                  back={props.path+'/profile'}
                />
              )
            }, {
              path: '/',
              onEnter: () => props.reset(props.currentUser),
              render: () => (
                <ProfileShow
                  path={`${props.path}/profile`}
                  name={selectors.STORE_NAME}
                  user={props.currentUser}
                />
              )
            }
          ]}
        />
      </ContentSizing>
    </ContextPage>
  )
}

ContextProfile.propTypes = {
  path: T.string.isRequired,
  currentUser: T.shape({
    username: T.string.isRequired
  }),
  reset: T.func.isRequired
}

export {
  ContextProfile
}
