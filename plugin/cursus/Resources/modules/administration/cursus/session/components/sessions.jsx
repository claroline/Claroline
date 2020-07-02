import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/session/store'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'

const SessionsComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.sessions.list'}
    fetch={{
      url: ['apiv2_cursus_session_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/sessions/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `${props.path}/sessions/form/${rows[0].id}`
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-plus-square',
        label: trans('invite_learners_to_session', {}, 'cursus'),
        scope: ['object'],
        callback: () => props.inviteAll(rows[0].id)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-graduation-cap',
        label: trans('generate_session_certificates', {}, 'cursus'),
        scope: ['object'],
        callback: () => props.generateAllCertificates(rows[0].id)
      }
    ]}
    delete={{
      url: ['apiv2_cursus_session_delete_bulk']
    }}
    definition={SessionList.definition}
    card={SessionList.card}
  />

SessionsComponent.propTypes = {
  path: T.string.isRequired,
  inviteAll: T.func.isRequired,
  generateAllCertificates: T.func.isRequired
}

const Sessions = connect(
  null,
  (dispatch) => ({
    inviteAll(sessionId) {
      dispatch(actions.inviteAll(sessionId))
    },
    generateAllCertificates(sessionId) {
      dispatch(actions.generateAllCertificates(sessionId))
    }
  })
)(SessionsComponent)

export {
  Sessions
}
