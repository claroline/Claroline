import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {showBreadcrumb} from '#/main/app/layout/utils'

import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/community/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/account/parameters/store/selectors'

const ParametersMain = (props) =>
  <UserPage
    showBreadcrumb={showBreadcrumb()}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('my_account'),
        target: '/account'
      }, {
        type: LINK_BUTTON,
        label: trans('parameters'),
        target: '/account/parameters'
      }
    ]}
    title={trans('parameters')}
    user={props.currentUser}
  >
    <ContentTitle
      title={trans('parameters')}
      style={{marginTop: 60}}
    />

    <FormData
      name={selectors.STORE_NAME}
      target={['apiv2_user_update', {id: props.currentUser.id}]}
      buttons={true}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.mailNotified',
              type: 'boolean',
              label: trans('get_mail_notifications', {address: props.currentUser.email}),
              // I need to calculate value because the current user is not mounted in the form
              calculated: (data) => undefined !== get(data, 'meta.mailNotified') ? get(data, 'meta.mailNotified') : get(props.currentUser, 'meta.mailNotified')
            }, {
              name: 'meta.locale',
              type: 'locale',
              label: trans('language'),
              required: true,
              // I need to calculate value because the current user is not mounted in the form
              calculated: (data) => undefined !== get(data, 'meta.locale') ? get(data, 'meta.locale') : get(props.currentUser, 'meta.locale'),
              options: {
                onlyEnabled: true
              }
            }
          ]
        }
      ]}
    />
  </UserPage>

ParametersMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  ParametersMain
}
