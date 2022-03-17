import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, displayDate} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {route} from '#/plugin/agenda/tools/agenda/routing'
import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'

const EventPage = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: props.event.name,
        target: props.path+'/event/'+props.event.id
      }
    ]}
    title={props.event.name}
    subtitle={displayDate(props.event.start, true, true)}
    poster={get(props.event, 'thumbnail.url')}
    primaryAction="show-calendar"
    actions={[
      {
        name: 'show-calendar',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-calendar',
        label: trans('show-calendar', {}, 'actions'),
        target: route(props.path, 'month', props.event.start),
        primary: true
      }, {
        name: 'edit',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        modal: [MODAL_EVENT_PARAMETERS, {
          event: props.event,
          onSave: props.reload
        }],
        displayed: hasPermission('edit', props.event)
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete', {}, 'actions'),
        callback: () => props.delete(props.event).then(() => {
          props.reload(props.event)
          props.history.push(route(props.path, 'month', props.event.start))
        }),
        dangerous: true,
        displayed: hasPermission('delete', props.event)
      }
    ].concat(props.actions)}

    meta={{
      title: `${trans('agenda', {}, 'tools')} - ${props.event.name}`,
      description: props.event.description
    }}
  >
    {props.children}
  </ToolPage>

EventPage.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,
  actions: T.arrayOf(T.object),
  children: T.node,
  // from store
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  delete: T.func.isRequired
}

EventPage.defaultProps = {
  actions: []
}

export {
  EventPage
}
