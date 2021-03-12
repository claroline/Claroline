import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, displayDate} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {constants} from '#/plugin/agenda/event/constants'
import {route} from '#/plugin/agenda/tools/agenda/routing'
import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'

const AgendaEvent = props => {
  if (!props.event) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons votre évènement..."
      />
    )
  }

  return (
    <PageFull
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb('agenda', props.currentContext.type, props.currentContext.data), [
        {
          type: LINK_BUTTON,
          label: props.event.title,
          target: props.path+'/event/'+props.event.id
        }
      ])}
      title={props.event.title}
      subtitle={displayDate(props.event.start, true, true)}
      poster={get(props.event, 'thumbnail.url')}
      toolbar="show-calendar | edit | fullscreen more"
      actions={[
        {
          name: 'show-calendar',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-calendar',
          label: trans('show-calendar', {}, 'actions'),
          target: route(props.path, 'month', props.referenceDate),
          primary: true
        }, {
          name: 'mark-done',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-check',
          label: trans('mark-as-done', {}, 'actions'),
          callback: () => props.markDone(props.event),
          displayed: constants.EVENT_TYPE_TASK === props.event.meta.type && !props.event.meta.done
        }, {
          name: 'mark-todo',
          type: CALLBACK_BUTTON,
          label: trans('mark-as-todo', {}, 'actions'),
          callback: () => props.markTodo(props.event),
          displayed: constants.EVENT_TYPE_TASK === props.event.meta.type && props.event.meta.done
        }, {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_EVENT_PARAMETERS, {
            event: props.event,
            onSave: props.update
          }],
          displayed: hasPermission('edit', props.event)
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          callback: () => props.delete(props.event, () => props.history.push(route(props.path, 'month', props.referenceDate))),
          dangerous: true,
          displayed: hasPermission('delete', props.event)
        }
      ]}

      meta={{
        title: `${trans('agenda', {}, 'tools')} - ${props.event.title}`,
        description: props.event.description
      }}
    >
      <DetailsData
        data={props.event}
        meta={true}
        sections={[{
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'dates',
              type: 'date-range',
              label: trans('date'),
              calculated: (event) => [event.start || null, event.end || null],
              options: {
                time: true
              }
            }, {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'guests',
              type: 'users',
              label: trans('guests')
            }
          ]
        }]}
      />
    </PageFull>
  )
}

AgendaEvent.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  referenceDate: T.string,
  event: T.shape(
    EventTypes.propTypes
  ),

  update: T.func.isRequired,
  delete: T.func.isRequired,
  markDone: T.func.isRequired,
  markTodo: T.func.isRequired
}

export {
  AgendaEvent
}
