import React, {Fragment, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {getInfo, isFull} from '#/plugin/cursus/utils'
import {MODAL_REGISTRATION_PARAMETERS} from '#/plugin/cursus/registration/modals/parameters'
import {SessionCard} from '#/plugin/cursus/session/components/card'
import {Alert} from '#/main/app/components/alert'

const RegistrationModal = props => {
  let initialSession = props.session ? props.session : null
  if (isEmpty(initialSession) && !isEmpty(props.available) && 1 === props.available.length) {
    initialSession = props.available[0]
  }
  const [activeSession, setActiveSession] = useState(initialSession)

  return (
    <Modal
      {...omit(props, 'path', 'course', 'session', 'register')}
      icon="fa fa-fw fa-user-plus"
      title={trans('registration')}
      subtitle={getInfo(props.course, activeSession, 'name')}
      poster={getInfo(props.course, activeSession, 'poster')}
      size="lg"
    >
      {(!activeSession && isEmpty(props.available)) &&
        <div className="modal-body">
          <h2 className="h3 text-center">{trans('no_available_session', {}, 'cursus')}</h2>
          <p className="lead text-center mb-00">{trans('no_available_session_help', {}, 'cursus')}</p>
        </div>
      }

      {(!activeSession && !isEmpty(props.available)) &&
        <div className="modal-body lead text-center">
          <p className="lead text-center">Veuillez choisir à quelle session vous désirez vous inscrire.</p>

          <div className="d-flex flex-column gap-1">
            {props.available.map(session =>
              <SessionCard
                key={session.id}
                data={session}
                primaryAction={{
                  type: CALLBACK_BUTTON,
                  callback: () => setActiveSession(session)
                }}
              />
            )}
          </div>
        </div>
      }

      {activeSession &&
        <Fragment>
          {isFull(activeSession) &&
            <div className="modal-body">
              <Alert type="warning" title={trans('session_full', {}, 'cursus')}>
                {trans('Vous pouvez vous inscrire en liste d\'attente ou parcourir les autres sessions.', {}, 'cursus')}
              </Alert>
            </div>
          }

          <DetailsData
            flush={true}
            data={activeSession}
            definition={[
              {
                title: trans('general'),
                primary: true,
                fields: [
                  {
                    name: 'restrictions.dates',
                    type: 'date-range',
                    label: trans('date')
                  }, {
                    name: 'description',
                    type: 'html',
                    label: trans('description')
                  }, {
                    name: 'location',
                    type: 'location',
                    label: trans('location'),
                    placeholder: trans('online_session', {}, 'cursus'),
                    displayed: (session) => !!get(session, 'location')
                  }, {
                    name: 'available',
                    type: 'string',
                    label: trans('available_seats', {}, 'cursus'),
                    displayed: (session) => !!get(session, 'restrictions.users'),
                    calculated: (session) => (get(session, 'restrictions.users') - get(session, 'participants.learners')) + ' / ' + get(session, 'restrictions.users')
                  }
                ]
              }
            ]}
          />
        </Fragment>
      }

      <Toolbar
        className="btn-group-vertical"
        variant="btn"
        buttonName="modal-btn"
        actions={[
          {
            name: 'show_other_sessions',
            type: CALLBACK_BUTTON,
            label: trans('show_other_sessions', {}, 'actions'),
            callback: () => setActiveSession(null),
            displayed: activeSession && !isEmpty(props.available) && 1 < props.available.length
          }, {
            name: 'register_waiting_list',
            type: CALLBACK_BUTTON,
            //primary: true,
            label: trans('register_waiting_list', {}, 'actions'),
            callback: () => {
              props.register(props.course, activeSession ? activeSession.id : null)
              props.fadeModal()
            },
            displayed: isEmpty(get(props.course, 'registration.form', [])) && (
              // no session but course pending list is enabled
              (!activeSession && get(props.course, 'registration.pendingRegistrations')) ||
              // session is full with pending list enabled
              activeSession && isFull(activeSession) && get(activeSession, 'registration.pendingRegistrations')
            )
          }, {
            name: 'register_waiting_list',
            type: MODAL_BUTTON,
            label: trans('register_waiting_list', {}, 'actions'),
            modal: [MODAL_REGISTRATION_PARAMETERS, {
              course: props.course,
              session: activeSession,
              onSave: (registrationData) => {
                props.register(props.course, activeSession ? activeSession.id : null, registrationData.data)
              }
            }],
            displayed: !isEmpty(get(props.course, 'registration.form', [])) && (
              // no session but course pending list is enabled
              (!activeSession && get(props.course, 'registration.pendingRegistrations')) ||
              // session is full with pending list enabled
              activeSession && isFull(activeSession) && get(activeSession, 'registration.pendingRegistrations')
            )
          }, {
            name: 'self_register',
            type: CALLBACK_BUTTON,
            primary: true,
            label: trans('self_register', {}, 'actions'),
            callback: () => {
              props.register(props.course, activeSession ? activeSession.id : null)
              props.fadeModal()
            },
            size: 'lg',
            displayed: isEmpty(get(props.course, 'registration.form', [])) && (activeSession && !isFull(activeSession))
          }, {
            name: 'self_register',
            type: MODAL_BUTTON,
            primary: true,
            label: trans('self_register', {}, 'actions'),
            onClick: props.fadeModal,
            modal: [MODAL_REGISTRATION_PARAMETERS, {
              course: props.course,
              session: activeSession,
              onSave: (registrationData) => {
                props.register(props.course, activeSession ? activeSession.id : null, registrationData.data)
              }
            }],
            size: 'lg',
            displayed: !isEmpty(get(props.course, 'registration.form', [])) && (activeSession && !isFull(activeSession))
          }
        ]}
      />
    </Modal>
  )
}

RegistrationModal.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  session: T.shape(
    SessionTypes.propTypes
  ),
  available: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  register: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  RegistrationModal
}
