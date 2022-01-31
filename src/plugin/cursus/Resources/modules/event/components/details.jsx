import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {EventAbout} from '#/plugin/cursus/event/components/about'
import {EventParticipants} from '#/plugin/cursus/event/containers/participants'

const EventDetails = (props) =>
  <Fragment>
    <header className="row content-heading">
      <ContentTabs
        backAction={{
          type: LINK_BUTTON,
          target: props.path,
          exact: true
        }}
        sections={[
          {
            name: 'about',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-info',
            label: trans('about'),
            target: `${props.path}/${props.event.id}`,
            exact: true
          }, {
            name: 'participants',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-users',
            label: trans('participants'),
            target: `${props.path}/${props.event.id}/participants`,
            displayed: props.isAuthenticated
          }
        ]}
      />
    </header>

    <Routes
      path={props.path+'/'+props.event.id}
      routes={[
        {
          path: '/',
          exact: true,
          render: () => (
            <EventAbout
              path={props.path}
              event={props.event}
              registration={props.registration}
              register={props.register}
            />
          )
        }, {
          path: '/participants',
          disabled: !props.isAuthenticated,
          render: () => (
            <EventParticipants
              path={props.path}
              event={props.event}
            />
          )
        }
      ]}
    />
  </Fragment>

EventDetails.propTypes = {
  path: T.string.isRequired,
  isAuthenticated: T.bool.isRequired,
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  registration: T.shape({

  }),
  register: T.func.isRequired
}

export {
  EventDetails
}
