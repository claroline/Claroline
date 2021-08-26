import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDuration, displayDate, now} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {currency} from '#/main/app/intl/currency'
import {hasPermission} from '#/main/app/security'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, POPOVER_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentTitle} from '#/main/app/content/components/title'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {LocationCard} from '#/main/core/data/types/location/components/card'
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
    .filter(session => !props.activeSession || props.activeSession.id !== session.id)

  return (
    <div className="row">
      <div className="col-md-3">
        {!isEmpty(props.activeSession) &&
          <div className="panel panel-default">
            <ul className="list-group list-group-values">
              <li className="list-group-item">
                {trans('status')}

                {get(props.activeSession, 'restrictions.dates[0]') > now() &&
                  <span className="value text-muted">
                    {trans('session_not_started', {}, 'cursus')}
                  </span>
                }

                {(get(props.activeSession, 'restrictions.dates[0]') <= now() && get(props.activeSession, 'restrictions.dates[1]') >= now()) &&
                  <span className="value text-success">
                    {trans('session_in_progress', {}, 'cursus')}
                  </span>
                }

                {get(props.activeSession, 'restrictions.dates[1]') < now() &&
                  <span className="value text-danger">
                    {trans('session_ended', {}, 'cursus')}
                  </span>
                }
              </li>

              <li className="list-group-item">
                {trans('start_date')}

                <span className="value">
                  {get(props.activeSession, 'restrictions.dates[0]') ?
                    displayDate(get(props.activeSession, 'restrictions.dates[0]')) :
                    trans('empty_value')
                  }
                </span>
              </li>

              <li className="list-group-item">
                {trans('end_date')}

                <span className="value">
                  {get(props.activeSession, 'restrictions.dates[1]') ?
                    displayDate(get(props.activeSession, 'restrictions.dates[1]')) :
                    trans('empty_value')
                  }
                </span>
              </li>
            </ul>
          </div>
        }

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
                      icon="fa fa-fw fa-info-circle"
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
          </Fragment>
        }

        <section className="overview-user-actions">
          {!getInfo(props.course, props.activeSession, 'registration.selfRegistration') &&
            <Alert type="warning">{trans('registration_requires_manager', {}, 'cursus')}</Alert>
          }

          {!isEmpty(props.activeSession)
            && isEmpty(props.activeSessionRegistration)
            && getInfo(props.course, props.activeSession, 'registration.selfRegistration')
            && !get(props.activeSession, 'registration.autoRegistration') &&
            <Button
              className="btn btn-block btn-emphasis"
              type={MODAL_BUTTON}
              label={trans(isFull(props.activeSession) ? 'register_waiting_list' : 'self_register', {}, 'actions')}
              modal={[MODAL_COURSE_REGISTRATION, {
                path: props.path,
                course: props.course,
                session: props.activeSession,
                register: props.register
              }]}
              primary={true}
            />
          }

          {isEmpty(props.activeSession) &&
            <Button
              className="btn btn-block btn-emphasis"
              type={LINK_BUTTON}
              label={trans('show_sessions', {}, 'actions')}
              target={route(props.path, props.course)+'/sessions'}
              primary={true}
            />
          }

          {!isEmpty(props.activeSession) &&
            <Button
              className="btn btn-block"
              type={LINK_BUTTON}
              label={trans('show_training_events', {}, 'actions')}
              target={route(props.path, props.course, props.activeSession)+'/events'}
            />
          }

          {(isFullyRegistered(props.activeSessionRegistration)
            || get(props.activeSession, 'registration.autoRegistration')
            || hasPermission('edit', props.course)
          ) && !isEmpty(getInfo(props.course, props.activeSession, 'workspace')) &&
            <Button
              className="btn btn-block"
              type={CALLBACK_BUTTON}
              label={trans('open-training', {}, 'actions')}
              callback={() => {
                const workspaceUrl = workspaceRoute(getInfo(props.course, props.activeSession, 'workspace'))
                if (get(props.activeSession, 'registration.autoRegistration') && !isFullyRegistered(props.activeSessionRegistration)) {
                  props.register(props.course, props.activeSession.id).then(() => props.history.push(workspaceUrl))
                } else {
                  props.history.push(workspaceUrl)
                }
              }}
            />
          }
        </section>
      </div>

      <div className="col-md-9">
        {props.activeSessionRegistration &&
          <CurrentRegistration
            sessionFull={isFull(props.activeSession)}
            registration={props.activeSessionRegistration}
          />
        }

        {!props.activeSessionRegistration && isFull(props.activeSession) &&
          <AlertBlock type="warning" title={trans('session_full', {}, 'cursus')}>
            {trans('session_full_help', {}, 'cursus')}
          </AlertBlock>
        }

        {!isHtmlEmpty(get(props.course, 'description')) &&
          <div className="panel panel-default">
            <ContentHtml className="panel-body">
              {get(props.course, 'description')}
            </ContentHtml>
          </div>
        }

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

        {props.activeSession && !isHtmlEmpty(get(props.activeSession, 'description')) &&
          <Fragment>
            <ContentTitle
              level={3}
              displayLevel={2}
              title={trans('session_info', {}, 'cursus')}
            />
            <div className="panel panel-default">
              <ContentHtml className="panel-body">
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

        <ContentTitle
          level={3}
          displayLevel={2}
          title={trans(!props.activeSession ? 'available_sessions' : 'other_available_sessions', {}, 'cursus')}
        />

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

        {isEmpty(availableSessions) &&
          <ContentPlaceholder
            icon="fa fa-fw fa-calendar-week"
            title={trans('no_available_session', {}, 'cursus')}
            help={trans('no_available_session_help', {}, 'cursus')}
          />
        }
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
  register: T.func.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

export {
  CourseAbout
}
