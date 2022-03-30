import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/tool/modals/parameters/store'

const MessagingParameters = props =>
  <FormData
    level={5}
    embedded={true}
    name={selectors.STORE_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'mailNotified',
            type: 'boolean',
            label: trans('get_mail_notifications', {address: props.currentUser.email}),
            calculated: (data) => undefined !== data.mailNotified ? data.mailNotified : props.mailNotified
          }
        ]
      }
    ]}
  />

MessagingParameters.propTypes = {
  mailNotified: T.bool,
  currentUser: T.shape({
    email: T.string.isRequired
  }).isRequired
}

export {
  MessagingParameters
}
