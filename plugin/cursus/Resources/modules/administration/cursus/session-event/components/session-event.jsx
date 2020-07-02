import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {
  selectors as formSelect,
  actions as formActions
} from '#/main/app/content/form/store'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {ListData} from '#/main/app/content/list/containers/data'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {MODAL_USERS} from '#/main/core/modals/users'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/session-event/store'
import {SessionEvent as SessionEventType} from '#/plugin/cursus/administration/cursus/prop-types'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'
import {SessionEventForm} from '#/plugin/cursus/administration/cursus/session-event/components/form'
import {SessionEventUserCard} from '#/plugin/cursus/administration/cursus/session-event/data/components/session-event-user-card'

const InvalidForm = (props) => props.new && props.sessionEvent.id ?
  <div>
    <div className="alert alert-danger">
      {trans('session_event_creation_impossible_no_session', {}, 'cursus')}
    </div>
    <ModalButton
      className="btn btn-groups-primary"
      style={{marginTop: 10}}
      primary={true}
      modal={[MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-cubes',
        title: trans('select_a_session', {}, 'cursus'),
        confirmText: trans('select', {}, 'actions'),
        name: selectors.STORE_NAME + '.sessions.picker',
        definition: SessionList.definition,
        card: SessionList.card,
        fetch: {
          url: ['apiv2_cursus_session_list'],
          autoload: true
        },
        onlyId: false,
        handleSelect: (selected) => props.selectSession(selected[0])
      }]}
    >
      <span className="fa fa-cubes icon-with-text-right" />
      {trans('select_a_session', {}, 'cursus')}
    </ModalButton>
  </div> :
  null

InvalidForm.propTypes = {
  new: T.bool.isRequired,
  sessionEvent: T.shape(SessionEventType.propTypes).isRequired,
  selectSession: T.func.isRequired
}

const SessionEventComponent = (props) => props.sessionEvent && props.sessionEvent.meta && props.sessionEvent.meta.session ?
  <SessionEventForm
    name={selectors.STORE_NAME + '.events.current'}
    buttons={true}
    target={(sessionEvent, isNew) => isNew ?
      ['apiv2_cursus_session_event_create'] :
      ['apiv2_cursus_session_event_update', {id: sessionEvent.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/events`,
      exact: true
    }}
  >
    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS, {
              title: trans('add_users', {}, 'cursus'),
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                callback: () => props.addUsers(props.sessionEvent.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.events.current.users'}
          fetch={{
            url: ['apiv2_cursus_session_event_list_users', {id: props.sessionEvent.id}],
            autoload: props.sessionEvent.id && !props.new
          }}
          actions={(rows) => [
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-plus-square',
              label: trans('invite_learners_to_session_event', {}, 'cursus'),
              scope: ['object', 'collection'],
              callback: () => props.inviteUsers(props.sessionEvent.id, rows)
            }, {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-graduation-cap',
              label: trans('generate_event_certificates', {}, 'cursus'),
              scope: ['object', 'collection'],
              callback: () => props.generateUsersCertificates(props.sessionEvent.id, rows)
            }
          ]}
          delete={{
            url: ['apiv2_cursus_session_event_remove_users']
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
              name: 'registrationDate',
              type: 'date',
              label: trans('registration_date', {}, 'cursus'),
              displayed: true
            }
          ]}
          card={SessionEventUserCard}
        />
      </FormSection>
    </FormSections>
  </SessionEventForm> :
  <InvalidForm
    new={props.new}
    sessionEvent={props.sessionEvent}
    selectSession={props.selectSession}
  />

SessionEventComponent.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  sessionEvent: T.shape(SessionEventType.propTypes).isRequired,
  selectSession: T.func.isRequired,
  addUsers: T.func.isRequired,
  inviteUsers: T.func.isRequired,
  generateUsersCertificates: T.func.isRequired
}

const SessionEvent = connect(
  (state) => ({
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.events.current')),
    sessionEvent: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.events.current'))
  }),
  (dispatch) => ({
    selectSession(session) {
      dispatch(formActions.updateProp(selectors.STORE_NAME + '.events.current', 'meta.session.id', session.id))
      dispatch(formActions.updateProp(selectors.STORE_NAME + '.events.current', 'registration.registrationType', session.registration.eventRegistrationType))
      dispatch(formActions.updateProp(selectors.STORE_NAME + '.events.current', 'restrictions.dates', session.restrictions.dates))
    },
    addUsers(sessionEventId, users) {
      dispatch(actions.addUsers(sessionEventId, users))
    },
    inviteUsers(sessionEventId, sessionEventUsers) {
      const users = sessionEventUsers.map(function (sessionEventUser) {
        return sessionEventUser['user']
      }, sessionEventUsers)
      dispatch(actions.inviteUsers(sessionEventId, users))
    },
    generateUsersCertificates(sessionEventId, sessionEventUsers) {
      const users = sessionEventUsers.map(function (sessionEventUser) {
        return sessionEventUser['user']
      }, sessionEventUsers)
      dispatch(actions.generateUsersCertificates(sessionEventId, users))
    }
  })
)(SessionEventComponent)

export {
  SessionEvent
}
