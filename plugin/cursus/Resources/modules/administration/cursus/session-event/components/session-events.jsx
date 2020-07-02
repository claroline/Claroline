import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/session-event/store'
import {SessionEventList} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-list'
import {SessionEventCard} from '#/plugin/cursus/administration/cursus/session-event/data/components/session-event-card'

const SessionEventsComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.events.list'}
    fetch={{
      url: ['apiv2_cursus_session_event_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/events/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `${props.path}/events/form/${rows[0].id}`
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-plus-square',
        label: trans('invite_learners_to_session_event', {}, 'cursus'),
        scope: ['object'],
        callback: () => props.inviteAll(rows[0].id)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-graduation-cap',
        label: trans('generate_event_certificates', {}, 'cursus'),
        scope: ['object'],
        callback: () => props.generateAllCertificates(rows[0].id)
      }
    ]}
    delete={{
      url: ['apiv2_cursus_session_delete_bulk']
    }}
    definition={SessionEventList.definition}
    card={SessionEventCard}
  />

SessionEventsComponent.propTypes = {
  path: T.string.isRequired,
  inviteAll: T.func.isRequired,
  generateAllCertificates: T.func.isRequired
}

const SessionEvents = connect(
  null,
  (dispatch) => ({
    inviteAll(sessionEventId) {
      dispatch(actions.inviteAll(sessionEventId))
    },
    generateAllCertificates(sessionId) {
      dispatch(actions.generateAllCertificates(sessionId))
    }
  })
)(SessionEventsComponent)

export {
  SessionEvents
}
