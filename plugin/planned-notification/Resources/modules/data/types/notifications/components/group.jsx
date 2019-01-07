import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {Notification as NotificationType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'
import {NotificationsInput} from '#/plugin/planned-notification/data/types/notifications/components/input'

const NotificationsGroup = props =>
  <FormGroup {...props}>
    <NotificationsInput {...props} />
  </FormGroup>

implementPropTypes(NotificationsGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(NotificationType.propTypes))
})

export {
  NotificationsGroup
}
