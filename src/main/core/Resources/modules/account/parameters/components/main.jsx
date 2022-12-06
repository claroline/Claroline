import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {FormData} from '#/main/app/content/form/containers/data'

import {User as UserTypes} from '#/main/community/prop-types'
import {AccountPage} from '#/main/app/account/containers/page'
import {route} from '#/main/app/account/routing'

import {selectors} from '#/main/core/account/parameters/store/selectors'

const ParametersMain = (props) =>
  <AccountPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('parameters'),
        target: route('parameters')
      }
    ]}
    title={trans('parameters')}
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
  </AccountPage>

ParametersMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  ParametersMain
}
