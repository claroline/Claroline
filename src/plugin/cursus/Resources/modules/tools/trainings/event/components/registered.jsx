import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/event/store'

const EventsRegistered = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('my_events', {}, 'cursus'),
      target: `${props.path}/registered`
    }]}
    subtitle={trans('my_events', {}, 'cursus')}
  >
    <ContentSizing size="full">
      <EventList
        flush={true}
        path={props.path}
        name={selectors.STORE_NAME}
        url={['apiv2_cursus_my_events']}
        customDefinition={[
          {
            name: 'session',
            label: trans('session', {}, 'cursus'),
            type: 'training_session',
            displayed: true
          }
        ]}
      />
    </ContentSizing>
  </ToolPage>

EventsRegistered.propTypes = {
  path: T.string.isRequired,
  contextId: T.string
}

export {
  EventsRegistered
}
