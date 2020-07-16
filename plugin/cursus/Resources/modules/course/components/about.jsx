import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDuration, displayDate, now} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'
import {CourseCard} from '#/plugin/cursus/course/components/card'
import {SessionCard} from '#/plugin/cursus/administration/cursus/session/data/components/session-card'
import {MODAL_COURSE_REGISTRATION} from '#/plugin/cursus/course/modals/registration'

function getInfo(course, session, path) {
  if (session && get(session, path)) {
    return get(session, path)
  } else if (get(course, path)) {
    return get(course, path)
  }

  return null
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
              {trans('max_participants', {}, 'cursus')}
              <span className="value">
                {props.course.restrictions.users ?
                  props.course.restrictions.users :
                  trans('empty_value')
                }
              </span>
            </li>

            <li className="list-group-item">
              {trans('duration')}
              <span className="value">
              {props.course.meta.duration ?
                displayDuration(props.course.meta.duration * 3600 * 24, true) :
                trans('empty_value')
              }
            </span>
            </li>
          </ul>
        </div>

        <Button
          className="btn btn-block btn-emphasis"
          type={MODAL_BUTTON}
          label={trans('self-register', {}, 'actions')}
          modal={[MODAL_COURSE_REGISTRATION, {
            course: props.course
          }]}
          primary={true}
        />

        {!isEmpty(props.course.workspace) &&
          <Button
            className="btn btn-block"
            type={LINK_BUTTON}
            label={trans('open-workspace', {}, 'actions')}
            target={workspaceRoute(props.course.workspace)}
          />
        }
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
                  {trans('session_open', {}, 'cursus')}
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

        <ContentTitle
          level={3}
          displayLevel={2}
          title="Formations liées"
          subtitle={props.course.parent ?
            'Cette formation fait partie de la formation' :
            'En vous inscrivant à cette formation, vous serez également inscrit aux formations suivantes'
          }
        />

        {props.course.parent &&
          <CourseCard
            style={{marginBottom: '5px'}}
            orientation="row"
            size="xs"
            data={props.course.parent}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(props.course.parent)
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

        {props.course.children.map(child =>
          <CourseCard
            key={child.id}
            style={{marginBottom: '5px'}}
            orientation="row"
            size="xs"
            data={child}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(child)
            }}
          />
        )}

        {!isEmpty(availableSessions) &&
          <ContentTitle
            level={3}
            displayLevel={2}
            title="Autres sessions disponibles"
          />
        }

        {availableSessions.map((session, index) =>
          <SessionCard
            key={session.id}
            style={{marginBottom: index === props.availableSessions.length - 1 ? 20 : 5}}
            orientation="row"
            size="xs"
            data={session}
            primaryAction={{
              type: LINK_BUTTON,
              target: route(props.course, session)
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
  activeSession: T.shape({
    id: T.string.isRequired
  }),
  availableSessions: T.arrayOf(T.shape({
    // TODO : propTypes
  }))
}

export {
  CourseAbout
}
