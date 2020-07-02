import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {DetailsData} from '#/main/app/content/details/components/data'
import {Button} from '#/main/app/action/components/button'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {
  Session as SessionType,
  SessionUser as SessionUserType,
  SessionQueue as SessionQueueType,
  Parameters as ParametersType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {selectors as cursusSelectors} from '#/plugin/cursus/tools/cursus/store'
import {actions, selectors} from '#/plugin/cursus/tools/cursus/catalog/session/store'
import {SessionEventList} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-list'
import {SessionEventCard} from '#/plugin/cursus/administration/cursus/session-event/data/components/session-event-card'

const SessionComponent = (props) => props.session && props.session.meta && props.session.meta.course ?
  <div id="catalog-session">
    <h3>
      {props.session.name}
    </h3>

    {!props.parameters.cursus.disable_session_registration &&
    props.currentUser &&
    props.session.registration.publicRegistration &&
    !props.sessionUser &&
    !props.isFull &&
    !props.sessionQueue &&
      <Button
        type={CALLBACK_BUTTON}
        icon="fa fa-sign-in"
        label={trans('register')}
        className="page-actions-btn btn-sm pull-right"
        primary={true}
        callback={() => props.register(props.session.id)}
      />
    }
    {props.sessionUser && props.session.meta.workspace &&
      <Button
        type={LINK_BUTTON}
        icon="fa fa-book"
        label={trans('workspace')}
        className="page-actions-btn btn-sm pull-right"
        primary={true}
        target={workspaceRoute(props.session.meta.workspace)}
      />
    }

    {props.sessionQueue &&
      <div className="alert alert-info">
        {trans('registration_pending_for_validation', {}, 'cursus')}
      </div>
    }

    <DetailsData
      data={props.session}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              label: trans('name'),
              type: 'string'
            }, {
              name: 'code',
              label: trans('code'),
              type: 'string'
            }, {
              name: 'meta.course.title',
              label: trans('course', {}, 'cursus'),
              type: 'string'
            }, {
              name: 'description',
              label: trans('description'),
              type: 'html'
            }, {
              name: 'restrictions.dates[0]',
              label: trans('start_date'),
              type: 'date'
            }, {
              name: 'restrictions.dates[1]',
              label: trans('end_date'),
              type: 'date'
            }, {
              name: 'restrictions.maxUsers',
              type: 'number',
              label: trans('maxUsers')
            }
          ]
        }
      ]}
    />

    {!props.parameters.cursus.disable_session_event_registration &&
      <FormSections
        level={3}
        defaultOpened="session-events"
      >
        <FormSection
          id="session-events"
          className="embedded-list-section"
          title={trans('session_events', {}, 'cursus')}
        >
          <ListData
            name={cursusSelectors.STORE_NAME + '.catalog.events'}
            fetch={{
              url: ['apiv2_cursus_session_list_events', {id: props.session.id}],
              autoload: true
            }}
            actions={(rows) => [
              {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-sign-in',
                label: trans('register'),
                scope: ['object'],
                displayed: constants.REGISTRATION_PUBLIC === rows[0].registration.registrationType,
                disabled: props.eventsRegistration[rows[0].id] || (
                  rows[0].meta.set &&
                  undefined !== props.eventsRegistration[rows[0].meta.set] &&
                  0 >= props.eventsRegistration[rows[0].meta.set]
                ),
                callback: () => props.registerToEvent(rows[0].id)
              }
            ]}
            definition={SessionEventList.definition}
            card={SessionEventCard}
          />
        </FormSection>
      </FormSections>
    }
  </div> :
  null

SessionComponent.propTypes = {
  currentUser: T.object,
  parameters: T.shape(ParametersType.propTypes).isRequired,
  session: T.shape(SessionType.propTypes).isRequired,
  sessionUser: T.shape(SessionUserType.propTypes),
  sessionQueue: T.shape(SessionQueueType.propTypes),
  isFull: T.bool.isRequired,
  eventsRegistration: T.object.isRequired,
  register: T.func.isRequired,
  registerToEvent: T.func.isRequired
}

const Session = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    parameters: cursusSelectors.parameters(state),
    session: selectors.session(state),
    sessionUser: selectors.sessionUser(state),
    sessionQueue: selectors.sessionQueue(state),
    isFull: selectors.isFull(state),
    eventsRegistration: selectors.eventsRegistration(state)
  }),
  (dispatch) => ({
    register(sessionId) {
      dispatch(actions.register(sessionId))
    },
    registerToEvent(eventId) {
      dispatch(actions.registerToEvent(eventId))
    }
  })
)(SessionComponent)

export {
  Session
}
