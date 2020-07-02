import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/queue/store'
import {SessionQueueCard} from '#/plugin/cursus/administration/cursus/queue/data/components/session-queue-card'

const QueuesComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.queues.list'}
    fetch={{
      url: ['apiv2_cursus_session_list_queues'],
      autoload: true
    }}
    actions={(rows) => [
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check-square-o',
        label: trans('validate'),
        scope: ['object'],
        callback: () => props.validate(rows[0].id)
      }
    ]}
    delete={{
      url: ['apiv2_cursus_session_remove_queues']
    }}
    definition={[
      {
        name: 'user.firstName',
        type: 'string',
        label: trans('firstName'),
        displayed: true
      }, {
        name: 'user.lastName',
        type: 'string',
        label: trans('lastName'),
        displayed: true
      }, {
        name: 'session.name',
        alias: 'sessionName',
        type: 'string',
        label: trans('session', {}, 'cursus'),
        displayed: true
      }, {
        name: 'applicationDate',
        type: 'date',
        label: trans('application_date', {}, 'cursus'),
        displayed: true
      }, {
        name: 'status',
        type: 'string',
        label: trans('pending_for_validation', {}, 'cursus'),
        displayed: true,
        filterable: false,
        calculated: (rowData) => {
          const validation = []

          if (constants.VALIDATION === (rowData.status & constants.VALIDATION)) {
            validation.push(trans('registration_validation', {}, 'cursus'))
          }
          if (constants.VALIDATION_USER === (rowData.status & constants.VALIDATION_USER)) {
            validation.push(trans('user_validation', {}, 'cursus'))
          }
          if (constants.VALIDATION_VALIDATOR === (rowData.status & constants.VALIDATION_VALIDATOR)) {
            validation.push(trans('validators', {}, 'cursus'))
          }
          if (constants.VALIDATION_ORGANIZATION === (rowData.status & constants.VALIDATION_ORGANIZATION)) {
            validation.push(trans('organization_validation', {}, 'cursus'))
          }

          return validation.join(', ')
        }
      }
    ]}
    card={SessionQueueCard}
  />

QueuesComponent.propTypes = {
  validate: T.func.isRequired
}

const Queues = connect(
  null,
  (dispatch) => ({
    validate(queueId) {
      dispatch(actions.validate(queueId))
    }
  })
)(QueuesComponent)

export {
  Queues
}
