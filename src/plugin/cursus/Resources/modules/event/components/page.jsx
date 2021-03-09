import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {Event as EventTypes} from '#/plugin/cursus/prop-types'
import {MODAL_TRAINING_EVENT_ABOUT} from '#/plugin/cursus/event/modals/about'
import {MODAL_TRAINING_EVENT_PARAMETERS} from '#/plugin/cursus/event/modals/parameters'

const EventPage = (props) => {
  if (isEmpty(props.event)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('event_loading', {}, 'cursus')}
      />
    )
  }

  return (
    <PageFull
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb('training_events', props.currentContext.type, props.currentContext.data), props.path)}
      title={get(props.event, 'name')}
      poster={get(props.event, 'poster.url')}
      toolbar="edit | fullscreen more"
      actions={[
        {
          name: 'about',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('show-info', {}, 'actions'),
          modal: [MODAL_TRAINING_EVENT_ABOUT, {
            event: props.event
          }],
          scope: ['object'],
        }, {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_TRAINING_EVENT_PARAMETERS, {
            event: props.event,
            onSave: () => props.reload(props.event.id)
          }],
          scope: ['object'],
          group: trans('management'),
          displayed: hasPermission('edit', props.event),
          primary: true
        }, {
          name: 'export-pdf',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-file-pdf-o',
          label: trans('export-pdf', {}, 'actions'),
          displayed: hasPermission('open', props.event),
          group: trans('transfer'),
          target: ['apiv2_cursus_event_download_pdf', {id: props.event.id}]
        }, {
          name: 'export-presences-empty',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-border-none',
          label: trans('export-presences-empty', {}, 'cursus'),
          displayed: hasPermission('edit', props.event),
          group: trans('presences', {}, 'cursus'),
          target: ['apiv2_cursus_event_presence_download', {id: props.event.id, filled: 0}]
        }, {
          name: 'export-presences-filled',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-border-all',
          label: trans('export-presences-filled', {}, 'cursus'),
          displayed: hasPermission('edit', props.event),
          group: trans('presences', {}, 'cursus'),
          target: ['apiv2_cursus_event_presence_download', {id: props.event.id, filled: 1}]
        }
      ]}

      meta={{
        title: `${trans('training_events', {}, 'tools')} - ${props.event.name}`,
        description: props.event.description
      }}
    >
      {props.children}
    </PageFull>
  )
}

EventPage.propTypes = {
  path: T.array,
  basePath: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  primaryAction: T.string,
  actions: T.array,
  event: T.shape(
    EventTypes.propTypes
  ),
  children: T.any
}

EventPage.defaultProps = {
  path: []
}

export {
  EventPage
}
