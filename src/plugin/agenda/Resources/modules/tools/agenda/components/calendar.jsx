import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {now} from '#/main/app/intl/date'
import {LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {MODAL_EVENT_CREATION} from '#/plugin/agenda/event/modals/creation'

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
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add-event', {}, 'actions'),
          modal: [MODAL_EVENT_CREATION, {
            event: {
              start: now(false),
              workspace: !isEmpty(props.contextData) ? props.contextData : null
            },
            onSave: (event) => props.reload(event, true)
          }],
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
            render: () => createElement(currentView.component, {
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
              reload: props.reload
            })
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
  reload: T.func.isRequired
}

export {
  AgendaCalendar
}
