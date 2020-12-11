import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDuration, displayDate, now} from '#/main/app/intl'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {LocationCard} from '#/main/core/user/data/components/location-card'
import {ResourceCard} from '#/main/core/resource/components/card'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {route} from '#/plugin/cursus/routing'
import {getInfo, isFullyRegistered, isFull} from '#/plugin/cursus/utils'
import {constants} from '#/plugin/cursus/constants'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseCard} from '#/plugin/cursus/course/components/card'
import {SessionCard} from '#/plugin/cursus/session/components/card'
import {MODAL_COURSE_REGISTRATION} from '#/plugin/cursus/course/modals/registration'

const CurrentRegistration = (props) => {
  let registrationTitle = trans('session_registration_pending', {}, 'cursus')
  if (constants.TEACHER_TYPE === props.registration.type) {
    registrationTitle = trans('session_registration_tutor', {}, 'cursus')
  } else if (isFullyRegistered(props.registration)) {
    registrationTitle = trans('session_registration', {}, 'cursus')
  }

  return (
    <AlertBlock
      type={isFullyRegistered(props.registration) ? 'success' : 'warning'}
      title={trans(registrationTitle, {}, 'cursus')}
    >
      {props.sessionFull &&
        <div>{trans('session_registration_full_help', {}, 'cursus')}</div>
      }

      {!props.sessionFull && undefined !== props.registration.confirmed && !props.registration.confirmed &&
        <div>{trans('session_registration_pending_help', {}, 'cursus')}</div>
      }
      {!props.sessionFull && undefined !== props.registration.validated && !props.registration.validated &&
        <div>{trans('session_registration_manager_help', {}, 'cursus')}</div>
      }
    </AlertBlock>
  )
}

CurrentRegistration.propTypes = {
  sessionFull: T.bool,
  registration: T.shape({
    type: T.string.isRequired,
    confirmed: T.bool,
    validated: T.bool
  }).isRequired
}

const CourseAbout = (props) => {
  const availableSessions = props.availableSessions
    .filter(session => props.activeSession && props.activeSession.id !== session.id)

  return (
    <div className="row">
      <div className="col-md-3">
        <div className="panel panel-default">
          <ul className="list-group list-group-values">
            <li className="list-group-item">
              {trans('public_registration')}
              <span className="value">
                {getInfo(props.course, props.activeSession, 'registration.selfRegistration') ? trans('yes') : trans('no')}
              </span>
            </li>

            <li className="list-group-item">
              {trans('available_seats', {}, 'cursus')}

              {!getInfo(props.course, props.activeSession, 'restrictions.users') &&
                <span className="value">{trans('not_limited', {}, 'cursus')}</span>
              }

              {getInfo(props.course, props.activeSession, 'restrictions.users') && !props.activeSession &&
                <span className="value">
                  {get(props.course, 'restrictions.users')}
                </span>
              }

              {getInfo(props.course, props.activeSession, 'restrictions.users') && props.activeSession &&
                <span className="value">
                  {(get(props.activeSession, 'restrictions.users') - get(props.activeSession, 'participants.learners')) + ' / ' + get(props.activeSession, 'restrictions.users')}
                </span>
              }
            </li>

            <li className="list-group-item">
              {trans('duration')}
              <span className="value">
                {getInfo(props.course, props.activeSession, 'meta.duration') ?
                  displayDuration(getInfo(props.course, props.activeSession, 'meta.duration') * 3600 * 24, true) :
                  trans('empty_value')
                }
              </span>
            </li>
          </ul>
        </div>

        <ContentTitle
          level={4}
          displayLevel={3}
          title={trans('location')}
        />

        {isEmpty(get(props.activeSession, 'location')) &&
          <div className="component-container">
            <em className="text-muted">{trans('online_session', {}, 'cursus')}</em>
          </div>
        }

        {!isEmpty(get(props.activeSession, 'location')) &&
          <LocationCard
            className="component-container"
            size="xs"
            orientation="row"
            data={get(props.activeSession, 'location')}
          />
        }

        <section className="overview-user-actions">
          {!getInfo(props.course, props.activeSession, 'registration.selfRegistration') &&
            <Alert type="warning">{trans('registration_requires_manager', {}, 'cursus')}</Alert>
          }

          {isEmpty(props.activeSessionRegistration) && getInfo(props.course, props.activeSession, 'registration.selfRegistration') &&
            <Button
              className="btn btn-block btn-emphasis"
              type={MODAL_BUTTON}
              label={trans(!props.activeSession || isFull(props.activeSession) ? 'register_waiting_list' : 'self-register', {}, 'actions')}
              modal={[MODAL_COURSE_REGISTRATION, {
                path: props.path,
                course: props.course,
                session: props.activeSession,
                register: props.register
              }]}
              primary={true}
            />
          }

          {isFullyRegistered(props.activeSessionRegistration) && !isEmpty(getInfo(props.course, props.activeSession, 'workspace')) &&
            <Button
              className="btn btn-block"
              type={LINK_BUTTON}
              label={trans('open-workspace', {}, 'actions')}
              target={workspaceRoute(getInfo(props.course, props.activeSession, 'workspace'))}
            />
          }
        </section>
      </div>

      <div className="col-md-9">
        {!props.activeSession &&
          <ContentPlaceholder
            icon="fa fa-fw fa-calendar-week"
            title={trans('no_available_session', {}, 'cursus')}
            help={trans('no_available_session_help', {}, 'cursus')}
          />
        }

        {props.activeSession &&
          <div className="content-resume">
            <div className="content-resume-info content-resume-primary">
              <span className="text-muted">
                {trans('status')}
              </span>

              {get(props.activeSession, 'restrictions.dates[0]') > now() &&
                <h1 className="content-resume-title h2 text-muted">
                  {trans('session_not_started', {}, 'cursus')}
                </h1>
              }

              {(get(props.activeSession, 'restrictions.dates[0]') <= now() && get(props.activeSession, 'restrictions.dates[1]') >= now()) &&
                <h1 className="content-resume-title h2 text-success">
                  {trans('session_in_progress', {}, 'cursus')}
                </h1>
              }

              {get(props.activeSession, 'restrictions.dates[1]') < now() &&
                <h1 className="content-resume-title h2 text-danger">
                  {trans('session_closed', {}, 'cursus')}
                </h1>
              }
            </div>

            <div className="content-resume-info">
              <span className="text-muted">
                {trans('start_date')}
              </span>

              {get(props.activeSession, 'restrictions.dates[0]') &&
                <h1 className="content-resume-title h2">
                  {displayDate(get(props.activeSession, 'restrictions.dates[0]'))}
                </h1>
              }
            </div>

            <div className="content-resume-info">
              <span className="text-muted">
                {trans('end_date')}
              </span>

              {get(props.activeSession, 'restrictions.dates[1]') &&
                <h1 className="content-resume-title h2">
                  {displayDate(get(props.activeSession, 'restrictions.dates[1]'))}
                </h1>
              }
            </div>
          </div>
        }

        {props.activeSessionRegistration &&
          <CurrentRegistration
            sessionFull={isFull(props.activeSession)}
            registration={props.activeSessionRegistration}
          />
        }

        {!props.activeSessionRegistration && isFull(props.activeSession) &&
          <AlertBlock type="warning" title={trans('La session est complète.', {}, 'cursus')}>
            {trans('Toutes les nouvelles inscriptions seront automatiquement ajoutées en liste d\'attente.', {}, 'cursus')}
          </AlertBlock>
        }

        <div className="panel panel-default">
          <ContentHtml className="panel-body">
            {getInfo(props.course, props.activeSession, 'description') || trans('no_description')}
          </ContentHtml>
        </div>

        {!isEmpty(props.course.tags) &&
          <div className="component-container tags">
            {props.course.tags.map(tag =>
              <span key={tag} className="tag label label-info">
                <span className="fa fa-fw fa-tag icon-with-text-right" />
                {tag}
              </span>
            )}
          </div>
        }

        {props.activeSession && !isEmpty(get(props.activeSession, 'resources')) &&
          <ContentTitle
            level={3}
            displayLevel={2}
            title={trans('useful_links')}
          />
        }

        {get(props.activeSession, 'resources', []).map((resource, index) =>
          <ResourceCard
            key={resource.id}
            style={{marginBottom: index === props.activeSession.resources.length - 1 ? 20 : 5}}
            orientation="row"
            size="xs"
            data={resource}
            primaryAction={{
              type: LINK_BUTTON,
              target: resourceRoute(resource)
            }}
          />
        )}

        {(props.course.parent || !isEmpty(props.course.children)) &&
          <hr/>
        }

        {(props.course.parent || !isEmpty(props.course.children)) &&
          <ContentTitle
            level={3}
            displayLevel={2}
            title={trans('linked_trainings', {}, 'cursus')}
            subtitle={props.course.parent ?
              'Cette formation fait partie de la formation' :
              'En vous inscrivant à cette formation, vous serez également inscrit aux formations suivantes'
            }
          />
        }

        {props.course.parent &&
          <CourseCard
            style={{marginBottom: 20}}
            orientation="row"
            size="xs"
            data={props.course.parent}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(props.path, props.course.parent)
            }}
          />
        }

        {props.course.parent && !isEmpty(props.course.children) &&
          <ContentTitle
            level={3}
            displayLevel={2}
            subtitle="En vous inscrivant à cette formation, vous serez également inscrit aux formations suivantes"
          />
        }

        {props.course.children.map((child, index) =>
          <CourseCard
            key={child.id}
            style={{marginBottom: index === props.course.children.length - 1 ? 20 : 5}}
            orientation="row"
            size="xs"
            data={child}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(props.path, child)
            }}
          />
        )}

        {!isEmpty(availableSessions) &&
          <ContentTitle
            level={3}
            displayLevel={2}
            title={trans('other_available_session', {}, 'cursus')}
          />
        }

        {availableSessions.map((session, index) =>
          <SessionCard
            key={session.id}
            style={{marginBottom: index === availableSessions.length - 1 ? 20 : 5}}
            orientation="row"
            size="xs"
            data={session}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(props.path, props.course, session)
            }}
          />
        )}
      </div>
    </div>
  )
}

CourseAbout.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  activeSessionRegistration: T.shape({

  }),
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  register: T.func.isRequired
}

export {
  CourseAbout
}
