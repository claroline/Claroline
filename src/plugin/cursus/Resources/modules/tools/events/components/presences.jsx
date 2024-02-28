import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {ToolPage} from '#/main/core/tool/containers/page'

import {PresencesList} from '#/plugin/cursus/presence/components/list'
import {selectors} from '#/plugin/cursus/tools/events/store'

const EventsPresences = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('presences', {}, 'cursus'),
      target: `${props.path}/presences`
    }]}
    subtitle={trans('presences', {}, 'cursus')}
  >
    <ContentSizing size="full">
      <PresencesList
        flush={true}
        path={props.path}
        name={selectors.STORE_NAME+'.presences'}
        url={['apiv2_cursus_workspace_presence_list', {id: props.contextId}]}
        customDefinition={[
          {
            name: 'event',
            type: 'training_event',
            label: trans('session_event', {}, 'cursus'),
            displayed: true
          }, {
            name: 'event.start',
            alias: 'startDate',
            type: 'date',
            label: trans('start_date'),
            displayed: true,
            options: {
              time: true
            }
          }, {
            name: 'event.end',
            alias: 'endDate',
            type: 'date',
            label: trans('end_date'),
            options: {
              time: true
            },
            displayed: true
          }, {
            name: 'session',
            type: 'training_session',
            label: trans('session', {}, 'cursus')
          }
        ]}
      />
    </ContentSizing>
  </ToolPage>

EventsPresences.propTypes = {
  path: T.string.isRequired,
  contextId: T.string.isRequired
}

export {
  EventsPresences
}
