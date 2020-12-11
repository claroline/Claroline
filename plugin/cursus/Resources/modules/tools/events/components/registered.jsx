import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {ToolPage} from '#/main/core/tool/containers/page'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/events/store'

const EventsRegistered = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('my_events', {}, 'cursus'),
      target: `${props.path}/registered`
    }]}
    subtitle={trans('my_events', {}, 'cursus')}
  >
    <header className="row content-heading">
      <ContentTabs
        sections={[
          {
            name: 'current',
            type: LINK_BUTTON,
            label: trans('Actives', {}, 'cursus'),
            target: `${props.path}/registered/`,
            exact: true
          }, {
            name: 'ended',
            type: LINK_BUTTON,
            label: trans('Terminées', {}, 'cursus'),
            target: `${props.path}/registered/ended`
          }, {
            name: 'pending',
            type: LINK_BUTTON,
            label: trans('pending_registrations'),
            target: `${props.path}/registered/pending`,
            displayed: false
          }
        ]}
      />
    </header>

    <Routes
      path={`${props.path}/registered`}
      routes={[
        {
          path: '/',
          exact: true,
          onEnter: () => props.invalidateList(),
          render: () => {
            const Current = (
              <EventList
                path={props.path}
                name={selectors.LIST_NAME}
                url={['apiv2_cursus_my_events_active', {workspace: props.contextId}]}
              />
            )

            return Current
          }
        }, {
          path: '/ended',
          onEnter: () => props.invalidateList(),
          render: () => {
            const Ended = (
              <EventList
                path={props.path}
                name={selectors.LIST_NAME}
                url={['apiv2_cursus_my_events_ended', {workspace: props.contextId}]}
              />
            )

            return Ended
          }
        }, {
          path: '/pending',
          onEnter: () => props.invalidateList(),
          render: () => {
            const Pending = (
              <EventList
                path={props.path}
                name={selectors.LIST_NAME}
                url={['apiv2_cursus_my_events_pending', {workspace: props.contextId}]}
              />
            )

            return Pending
          }
        }
      ]}
    />
  </ToolPage>

EventsRegistered.propTypes = {
  path: T.string.isRequired,
  contextId: T.string,
  invalidateList: T.func.isRequired
}

export {
  EventsRegistered
}
