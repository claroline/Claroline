import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {
  selectors as formSelect,
  actions as formActions
} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {trans} from '#/main/core/translation'

import {SessionEvent as SessionEventType} from '#/plugin/cursus/administration/cursus/prop-types'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'
import {SessionEventForm} from '#/plugin/cursus/administration/cursus/session-event/components/form'

const InvalidForm = (props) => props.new && props.sessionEvent.id ?
  <div>
    <div className="alert alert-danger">
      {trans('session_event_creation_impossible_no_session', {}, 'cursus')}
    </div>
    <CallbackButton
      className="btn btn-block"
      primary={true}
      callback={() => props.selectSession()}
    >
      <span className="fa fa-cubes" />
      {trans('select_a_session', {}, 'cursus')}
    </CallbackButton>
  </div> :
  null

InvalidForm.propTypes = {
  new: T.bool.isRequired,
  sessionEvent: T.shape(SessionEventType.propTypes).isRequired,
  selectSession: T.func.isRequired
}

const SessionEventComponent = (props) => props.sessionEvent && props.sessionEvent.meta && props.sessionEvent.meta.session ?
  <SessionEventForm
    name="events.current"
    buttons={true}
    target={(sessionEvent, isNew) => isNew ?
      ['apiv2_cursus_session_event_create'] :
      ['apiv2_cursus_session_event_update', {id: sessionEvent.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/events',
      exact: true
    }}
  /> :
  <InvalidForm
    new={props.new}
    sessionEvent={props.sessionEvent}
    selectSession={props.selectSession}
  />

SessionEventComponent.propTypes = {
  new: T.bool.isRequired,
  sessionEvent: T.shape(SessionEventType.propTypes).isRequired,
  selectSession: T.func.isRequired
}

const SessionEvent = connect(
  (state) => ({
    new: formSelect.isNew(formSelect.form(state, 'events.current')),
    sessionEvent: formSelect.data(formSelect.form(state, 'events.current'))
  }),
  (dispatch) => ({
    selectSession() {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-cubes',
        title: trans('select_a_session', {}, 'cursus'),
        confirmText: trans('select', {}, 'actions'),
        name: 'sessions.picker',
        definition: SessionList.definition,
        card: SessionList.card,
        fetch: {
          url: ['apiv2_cursus_session_list'],
          autoload: true
        },
        onlyId: false,
        handleSelect: (selected) => {
          dispatch(formActions.updateProp('events.current', 'meta.session.id', selected[0].id))
          dispatch(formActions.updateProp('events.current', 'registration.registrationType', selected[0].registration.eventRegistrationType))
          dispatch(formActions.updateProp('events.current', 'restrictions.dates', selected[0].restrictions.dates))
        }
      }))
    }
  })
)(SessionEventComponent)

export {
  SessionEvent
}
