import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDate, displayDuration, now} from '#/main/app/intl'
import {getTimeDiff} from '#/main/app/intl/date'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentTitle} from '#/main/app/content/components/title'
import {LocationCard} from '#/main/core/data/types/location/components/card'
import {RoomCard} from '#/main/core/data/types/room/components/card'
import {ResourceCard} from '#/main/core/resource/components/card'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {isFullyRegistered, isFull} from '#/plugin/cursus/utils'
import {constants} from '#/plugin/cursus/constants'
import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {SessionCard} from '#/plugin/cursus/session/components/card'
import {MODAL_COURSE_REGISTRATION} from '#/plugin/cursus/course/modals/registration'

const CurrentRegistration = (props) => {
  let registrationTitle = trans('event_registration_pending', {}, 'cursus')
  if (constants.TEACHER_TYPE === props.registration.type) {
    registrationTitle = trans('event_registration_tutor', {}, 'cursus')
  } else if (isFullyRegistered(props.registration)) {
    registrationTitle = trans('event_registration', {}, 'cursus')
  }

  return (
    <AlertBlock
      type={isFullyRegistered(props.registration) ? 'success' : 'warning'}
      title={trans(registrationTitle, {}, 'cursus')}
    >
      {props.eventFull &&
        <div>{trans('event_registration_full_help', {}, 'cursus')}</div>
      }

      {!props.eventFull && undefined !== props.registration.confirmed && !props.registration.confirmed &&
        <div>{trans('event_registration_pending_help', {}, 'cursus')}</div>
      }
      {!props.eventFull && undefined !== props.registration.validated && !props.registration.validated &&
        <div>{trans('event_registration_manager_help', {}, 'cursus')}</div>
      }
    </AlertBlock>
  )
}

CurrentRegistration.propTypes = {
  eventFull: T.bool,
  registration: T.shape({
    type: T.string.isRequired,
    confirmed: T.bool,
    validated: T.bool
  }).isRequired
}

const EventAbout = (props) =>
  <div className="row">
    <div className="col-md-3">
      <div className="panel panel-default">
        <ul className="list-group list-group-values">
          <li className="list-group-item">
            {trans('registration')}
            <span className="value">
              {constants.REGISTRATION_TYPES[get(props.event, 'registration.registrationType', constants.REGISTRATION_MANUAL)]}
            </span>
          </li>

          <li className="list-group-item">
            {trans('available_seats', {}, 'cursus')}

            {!get(props.event, 'restrictions.users') &&
              <span className="value">{trans('not_limited', {}, 'cursus')}</span>
            }

            {get(props.event, 'restrictions.users') &&
              <span className="value">
                {(get(props.event, 'restrictions.users') - get(props.event, 'participants.learners')) + ' / ' + get(props.event, 'restrictions.users')}
              </span>
            }
          </li>

          <li className="list-group-item">
            {trans('duration')}
            <span className="value">
              {get(props.event, 'start') && get(props.event, 'end') ?
                displayDuration(getTimeDiff(get(props.event, 'start'), get(props.event, 'end')), true) :
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

      {isEmpty(get(props.event, 'location')) &&
        <div className="component-container">
          {isEmpty(get(props.event, 'locationUrl')) &&
            <em className="text-muted">{trans('online_session', {}, 'cursus')}</em>
          }

          {!isEmpty(get(props.event, 'locationUrl')) &&
            <a href={get(props.event, 'locationUrl')}>{get(props.event, 'locationUrl')}</a>
          }
        </div>
      }

      {!isEmpty(get(props.event, 'location')) &&
        <LocationCard
          className="component-container"
          size="xs"
          orientation="row"
          data={get(props.event, 'location')}
        />
      }

      {!isEmpty(get(props.event, 'room')) &&
        <RoomCard
          className="component-container"
          size="xs"
          orientation="row"
          data={get(props.event, 'room')}
        />
      }

      <section className="overview-user-actions">
        {constants.REGISTRATION_MANUAL === get(props.event, 'registration.registrationType') &&
          <Alert type="warning">{trans('registration_requires_manager', {}, 'cursus')}</Alert>
        }

        {constants.REGISTRATION_AUTO === get(props.event, 'registration.registrationType') &&
          <Alert type="warning">{trans('registration_auto', {}, 'cursus')}</Alert>
        }

        {!isFull(props.event) && isEmpty(props.registration) && constants.REGISTRATION_PUBLIC === get(props.event, 'registration.registrationType') &&
          <Button
            className="btn btn-block btn-emphasis"
            type={MODAL_BUTTON}
            label={trans('self_register', {}, 'actions')}
            modal={[MODAL_COURSE_REGISTRATION, {
              event: props.event,
              register: props.register
            }]}
            primary={true}
          />
        }

        {isFullyRegistered(props.registration) && !isEmpty(get(props.event, 'primaryResource')) &&
          <Button
            className="btn btn-block"
            type={LINK_BUTTON}
            label={trans('open-resource', {}, 'actions')}
            target={resourceRoute(get(props.event, 'primaryResource'))}
          />
        }
      </section>
    </div>

    <div className="col-md-9">
      <div className="content-resume">
        <div className="content-resume-info content-resume-primary">
          <span className="text-muted">
            {trans('status')}
          </span>

          {get(props.event, 'start') > now() &&
            <h1 className="content-resume-title h2 text-muted">
              {trans('session_not_started', {}, 'cursus')}
            </h1>
          }

          {(get(props.event, 'start') <= now() && get(props.event, 'end') >= now()) &&
            <h1 className="content-resume-title h2 text-success">
              {trans('session_in_progress', {}, 'cursus')}
            </h1>
          }

          {get(props.event, 'end') < now() &&
            <h1 className="content-resume-title h2 text-danger">
              {trans('session_ended', {}, 'cursus')}
            </h1>
          }
        </div>

        <div className="content-resume-info">
          <span className="text-muted">
            {trans('start_date')}
          </span>

          {get(props.event, 'start') &&
            <h1 className="content-resume-title h2">
              {displayDate(get(props.event, 'start'), false, true)}
            </h1>
          }
        </div>
      </div>

      {props.registration && (!isEmpty(props.registration.users) || !isEmpty(props.registration.groups)) &&
        <CurrentRegistration
          eventFull={isFull(props.event)}
          registration={!isEmpty(props.registration.users) ? props.registration.users[0] : props.registration.groups[0]}
        />
      }

      {!props.registration && isFull(props.event) &&
        <AlertBlock type="warning" title={trans('event_full', {}, 'cursus')}>
          {trans('event_full_help', {}, 'cursus')}
        </AlertBlock>
      }

      <div className="panel panel-default">
        <ContentHtml className="panel-body">
          {get(props.event, 'description') || trans('no_description')}
        </ContentHtml>
      </div>

      {!isEmpty(get(props.event, 'resources')) &&
        <ContentTitle
          level={3}
          displayLevel={2}
          title={trans('useful_links')}
        />
      }

      {get(props.event, 'resources', []).map((resource, index) =>
        <ResourceCard
          key={resource.id}
          style={{marginBottom: index === props.event.resources.length - 1 ? 20 : 5}}
          orientation="row"
          size="xs"
          data={resource}
          primaryAction={{
            type: LINK_BUTTON,
            target: resourceRoute(resource)
          }}
        />
      )}

      <hr/>

      <ContentTitle
        level={3}
        displayLevel={2}
        title={trans('session', {}, 'cursus')}
      />

      <SessionCard
        style={{marginBottom: 20}}
        orientation="row"
        size="xs"
        data={props.event.session}
      />
    </div>
  </div>

EventAbout.propTypes = {
  path: T.string.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  registration: T.shape({
    users: T.array,
    groups: T.array
  }),
  register: T.func.isRequired
}

export {
  EventAbout
}
