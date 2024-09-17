import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDate, now} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {currency} from '#/main/app/intl/currency'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button, Toolbar} from '#/main/app/action'
import {LINK_BUTTON, POPOVER_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentTitle} from '#/main/app/content/components/title'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {LocationCard} from '#/main/core/data/types/location/components/card'
import {ResourceCard} from '#/main/core/resource/components/card'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {route} from '#/plugin/cursus/routing'
import {getInfo, isFullyRegistered, isFull, getSessionRegistration, getCourseRegistration} from '#/plugin/cursus/utils'
import {constants} from '#/plugin/cursus/constants'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseCard} from '#/plugin/cursus/course/components/card'
import {SessionCard} from '#/plugin/cursus/session/components/card'

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
  const activeSessionRegistration = props.activeSession ? getSessionRegistration(props.activeSession, props.registrations) : null
  const courseRegistration = getCourseRegistration(props.registrations)

  const availableSessions = props.availableSessions
    .filter(session => (!props.activeSession || props.activeSession.id !== session.id) && !get(session, 'restrictions.hidden') && !get(session, 'meta.canceled'))

  return (
    <div className="row mt-3">
      <div className="col-md-3">
        <div className="card mb-3">
          <ul className="list-group list-group-flush list-group-values">
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
                  getInfo(props.course, props.activeSession, 'meta.duration') + ' ' + trans('hours') :
                  trans('empty_value')
                }
              </span>
            </li>

            {param('pricing.enabled') &&
              <li className="list-group-item">
                {trans('price')}
                <span className="value">
                  {getInfo(props.course, props.activeSession, 'pricing.price') || 0 === getInfo(props.course, props.activeSession, 'pricing.price') ?
                    currency(getInfo(props.course, props.activeSession, 'pricing.price')) :
                    trans('empty_value')
                  }

                  {getInfo(props.course, props.activeSession, 'pricing.description') &&
                    <Button
                      className="icon-with-text-left"
                      type={POPOVER_BUTTON}
                      icon="fa fa-fw fa-circle-info"
                      label={trans('show-info', {}, 'actions')}
                      tooltip="top"
                      popover={{
                        content: (
                          <ContentHtml>
                            {(getInfo(props.course, props.activeSession, 'pricing.description') || '')}
                          </ContentHtml>
                        ),
                        position: 'bottom'
                      }}
                    />
                  }
                </span>
              </li>
            }
          </ul>
        </div>

        {!isEmpty(props.activeSession) &&
          <Fragment>
            <ContentTitle
              level={4}
              displayLevel={3}
              title={trans('location')}
            />

            {isEmpty(get(props.activeSession, 'location')) &&
              <div className="text-secondary mb-3">{trans('online_session', {}, 'cursus')}</div>
            }

            {!isEmpty(get(props.activeSession, 'location')) &&
              <LocationCard
                className="mb-3"
                size="xs"
                orientation="row"
                data={get(props.activeSession, 'location')}
              />
            }
          </Fragment>
        }

        <section>
          {!isFullyRegistered(activeSessionRegistration) && !getInfo(props.course, props.activeSession, 'registration.selfRegistration') &&
            <Alert type="warning" >{trans('registration_requires_manager', {}, 'cursus')}</Alert>
          }

          <Toolbar
            className="d-grid gap-1 mb-3"
            variant="btn"
            actions={props.actions}
          />
        </section>
      </div>

      <div className="col-md-9">

        {get(props.course, 'meta.archived') === true &&
          <AlertBlock type="info" title={trans('course_archived_info', {}, 'cursus')}>
            {trans('course_archived_info_help', {}, 'cursus')}
          </AlertBlock>
        }

        {get(props.activeSession, 'meta.canceled') === true &&
          <AlertBlock type="info" title={trans('cancel_session_info', {}, 'actions')}>
            <ContentHtml>
              {get(props.activeSession, 'meta.cancelReason')}
            </ContentHtml>
          </AlertBlock>
        }

        {!isEmpty(props.activeSession) &&
          <div className="content-resume">
            <div className="content-resume-info content-resume-primary">
              <span className="text-secondary">
                {trans('status')}
              </span>

              {get(props.activeSession, 'restrictions.dates[0]') > now() &&
                <h1 className="content-resume-title h2 text-secondary">
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
                  {trans('session_ended', {}, 'cursus')}
                </h1>
              }
            </div>

            <div className="content-resume-info">
              <span className="text-secondary">
                {trans('start_date')}
              </span>

              <h1 className="content-resume-title h2">
                {get(props.activeSession, 'restrictions.dates[0]') ?
                  displayDate(get(props.activeSession, 'restrictions.dates[0]')) :
                  trans('empty_value')
                }
              </h1>
            </div>

            <div className="content-resume-info">
              <span className="text-secondary">
                {trans('end_date')}
              </span>

              <h1 className="content-resume-title h2">
                {get(props.activeSession, 'restrictions.dates[1]') ?
                  displayDate(get(props.activeSession, 'restrictions.dates[1]')) :
                  trans('empty_value')
                }
              </h1>
            </div>
          </div>
        }

        {courseRegistration &&
          <AlertBlock
            type="warning"
            title={trans('course_registration_pending', {}, 'cursus')}
          >
            {trans('course_registration_pending_help', {}, 'cursus')}
          </AlertBlock>
        }

        {activeSessionRegistration &&
          <CurrentRegistration
            sessionFull={isFull(props.activeSession)}
            registration={activeSessionRegistration}
          />
        }

        {!activeSessionRegistration && isFull(props.activeSession) &&
          <AlertBlock type="warning" title={trans('session_full', {}, 'cursus')}>
            {trans('session_full_help', {}, 'cursus')}
          </AlertBlock>
        }

        {!isHtmlEmpty(get(props.course, 'description')) &&
          <div className="card mb-3">
            <ContentHtml className="card-body">
              {get(props.course, 'description')}
            </ContentHtml>
          </div>
        }

        {!isEmpty(props.course.tags) &&
          <div className="component-container tags">
            {props.course.tags.map(tag =>
              <span key={tag} className="tag badge text-bg-primary">
                <span className="fa fa-fw fa-tag icon-with-text-right" />
                {tag}
              </span>
            )}
          </div>
        }

        {props.activeSession && !isHtmlEmpty(get(props.activeSession, 'description')) &&
          <Fragment>
            <ContentTitle
              level={3}
              displayLevel={2}
              title={trans('session_info', {}, 'cursus')}
            />
            <div className="card mb-3">
              <ContentHtml className="card-body">
                {get(props.activeSession, 'description')}
              </ContentHtml>
            </div>
          </Fragment>
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
            className={index === props.activeSession.resources.length - 1 ? 'mb-3' : 'mb-1'}
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
          <ContentTitle
            level={3}
            displayLevel={2}
            title={trans('linked_trainings', {}, 'cursus')}
            subtitle={trans(props.course.parent ? 'linked_trainings_parent' : 'linked_trainings_children', {}, 'cursus')}
          />
        }

        {props.course.parent &&
          <CourseCard
            className="mb-3"
            orientation="row"
            size="xs"
            data={props.course.parent}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(props.course.parent, null, props.path)
            }}
          />
        }

        {props.course.parent && !isEmpty(props.course.children) &&
          <ContentTitle
            level={3}
            displayLevel={2}
            subtitle={trans('linked_trainings_children', {}, 'cursus')}
          />
        }

        {props.course.children.map((child, index) =>
          <CourseCard
            key={child.id}
            className={index === props.course.children.length - 1 ? 'mb-3' : 'mb-1'}
            orientation="row"
            size="xs"
            data={child}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(child, null, props.path)
            }}
          />
        )}

        {!get(props.course, 'display.hideSessions') && !isEmpty(availableSessions) &&
          <Fragment>
            <ContentTitle
              level={3}
              displayLevel={2}
              title={trans(!props.activeSession ? 'available_sessions' : 'other_available_sessions', {}, 'cursus')}
            />

            {availableSessions.map((session, index) =>
              <SessionCard
                key={session.id}
                className={index === availableSessions.length - 1 ? 'mb-3' : 'mb-1'}
                orientation="row"
                size="xs"
                data={session}
                primaryAction={{
                  type: LINK_BUTTON,
                  target: route(props.course, session, props.path)
                }}
              />
            )}
          </Fragment>
        }
      </div>
    </div>
  )
}

CourseAbout.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  availableSessions: T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  registrations: T.shape({
    users: T.array.isRequired,
    groups: T.array.isRequired,
    pending: T.array.isRequired
  }),
  contextType: T.string,
  path: T.string,
  actions: T.array
}

export {
  CourseAbout
}
