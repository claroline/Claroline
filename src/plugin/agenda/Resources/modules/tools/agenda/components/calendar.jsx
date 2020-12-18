import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router'
import {now} from '#/main/app/intl/date'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Event as EventTypes} from '#/plugin/agenda/event/prop-types'
import {constants} from '#/plugin/agenda/event/constants'
import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'

import {route} from '#/plugin/agenda/tools/agenda/routing'
import {AGENDA_VIEWS} from '#/plugin/agenda/tools/agenda/views'

const AgendaCalendar = (props) => {
  const currentView = AGENDA_VIEWS[props.view]
  const currentRange = currentView.range(props.referenceDate)

  return (
    <ToolPage
      subtitle={currentView.display(props.referenceDate)}
      primaryAction="add | previous range next | today"
      actions={[
        {
          name: 'previous',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-chevron-left',
          label: trans('previous'),
          target: route(props.path, props.view, currentView.previous(props.referenceDate))
        }, {
          name: 'next',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-chevron-right',
          label: trans('next'),
          target: route(props.path, props.view, currentView.next(props.referenceDate))
        }, {
          name: 'range',
          type: MENU_BUTTON,
          icon: <span>{currentView.label}</span>,
          label: trans('change-calendar-view', {}, 'actions'),
          menu: {
            align: 'right',
            label: trans('display_modes', {}, 'agenda'),
            items: Object.keys(AGENDA_VIEWS).map(viewName => ({
              type: LINK_BUTTON,
              label: AGENDA_VIEWS[viewName].label,
              target: route(props.path, viewName, props.referenceDate)
            }))
          }
        }, {
          name: 'today',
          type: LINK_BUTTON,
          icon: <span>{trans('today')}</span>,
          label: trans('today'),
          target: route(props.path, props.view, now())
        }, {
          name: 'add',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add-event', {}, 'actions'),
          callback: () => props.create({
            start: now(false)
          }, props.contextData, props.currentUser),
          displayed: !isEmpty(props.currentUser),
          primary: true
        }
      ]}
    >
      <Routes
        path={props.path}
        routes={[
          {
            path: '/:view?/:year?/:month?/:day?',
            onEnter: (params = {}) => {
              // grab view from params
              let newView = props.view
              if (params.view) {
                newView = params.view
              }

              // grab reference date from params
              const newReference = moment(props.referenceDate)
              if (params.year) {
                newReference.year(params.year)

                if (params.month) {
                  newReference.month(params.month - 1)
                }

                if (params.day) {
                  newReference.date(params.day)
                }
              }

              props.changeView(newView, newReference)
            },
            render: () => {
              const CurrentView = createElement(currentView.component, {
                path: props.path,
                loaded: props.loaded,
                loadEvents: props.load,
                view: props.view,
                referenceDate: props.referenceDate,
                range: currentRange,
                previous: currentView.previous,
                next: currentView.next,
                create: (event) => props.create(event, props.contextData, props.currentUser),
                events: props.events,
                eventActions: (event) => [
                  {
                    name: 'mark-done',
                    type: CALLBACK_BUTTON,
                    icon: 'fa fa-fw fa-check',
                    label: trans('mark-as-done', {}, 'actions'),
                    callback: () => props.markDone(event),
                    displayed: constants.EVENT_TYPE_TASK === event.meta.type && !event.meta.done
                  }, {
                    name: 'mark-todo',
                    type: CALLBACK_BUTTON,
                    label: trans('mark-as-todo', {}, 'actions'),
                    callback: () => props.markTodo(event),
                    displayed: constants.EVENT_TYPE_TASK === event.meta.type && event.meta.done
                  }, {
                    name: 'edit',
                    type: MODAL_BUTTON,
                    label: trans('edit', {}, 'actions'),
                    modal: [MODAL_EVENT_PARAMETERS, {
                      event: event,
                      onSave: props.update
                    }],
                    displayed: hasPermission('edit', event)
                  }, {
                    name: 'delete',
                    type: CALLBACK_BUTTON,
                    label: trans('delete', {}, 'actions'),
                    callback: () => props.delete(event),
                    dangerous: true,
                    displayed: hasPermission('delete', event)
                  }
                ]
              })

              return CurrentView
            }
          }
        ]}
      />
    </ToolPage>
  )
}

AgendaCalendar.propTypes = {
  path: T.string.isRequired,
  contextData: T.shape({
    id: T.number
  }),
  currentUser: T.shape({
    // TODO : propTypes
  }),

  view: T.oneOf([
    'day',
    'week',
    'month',
    'year',
    'schedule',
    'list'
  ]).isRequired,
  referenceDate: T.object.isRequired,// moment date object
  changeView: T.func.isRequired,

  loaded: T.bool.isRequired,
  events: T.arrayOf(T.shape(
    EventTypes.propTypes
  )).isRequired,
  load: T.func.isRequired,
  create: T.func.isRequired,
  update: T.func.isRequired,
  delete: T.func.isRequired,
  markDone: T.func.isRequired,
  markTodo: T.func.isRequired
}

export {
  AgendaCalendar
}
