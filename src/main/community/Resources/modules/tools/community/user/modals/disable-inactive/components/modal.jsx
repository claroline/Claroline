import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

const DisableInactiveModal = (props) =>
  <FormDataModal
    {...omit(props, 'disableInactive')}
    icon="fa fa-fw fa-user-clock"
    title={trans('disable_inactive_users', {}, 'community')}
    save={(data) => props.disableInactive(data.lastActivity)}
    definition={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        help: trans('disable_inactive_users_help', {}, 'community'),
        fields: [
          {
            name: 'lastActivity',
            type: 'date',
            label: trans('last_activity'),
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
