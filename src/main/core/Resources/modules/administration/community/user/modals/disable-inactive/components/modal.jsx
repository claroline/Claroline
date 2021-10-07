import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

const DisableInactiveModal = (props) =>
  <FormDataModal
    {...omit(props, 'disableInactive')}
    icon="fa fa-fw fa-user-slash"
    title={trans('disable_inactive_users')}
    save={(data) => props.disableInactive(data.lastLogin)}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        help: trans('disable_inactive_users_help'),
        fields: [
          {
            name: 'lastLogin',
            type: 'date',
            label: trans('last_login'),
            required: true
          }
        ]
      }
    ]}
  />

DisableInactiveModal.propTypes = {
  disableInactive: T.func.isRequired
}

export {
  DisableInactiveModal
}
