import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {now} from '#/main/app/intl/date'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {route} from '#/plugin/agenda/tools/agenda/routing'
import {AGENDA_VIEWS} from '#/plugin/agenda/tools/agenda/views'
import {MODAL_AGENDA_PARAMETERS} from '#/plugin/agenda/tools/agenda/modals/parameters'

const AgendaTool = (props) => {
  const currentView = AGENDA_VIEWS[props.view]
  const currentRange = currentView.range(props.referenceDate)

  return (
    <ToolPage
      subtitle={currentView.display(props.referenceDate)}
      toolbar="add | previous range next | today | more"
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
          callback: () => props.createEvent({
            start: now(false)
          }, props.currentUser),
          displayed: !isEmpty(props.currentUser),
          primary: true
        }, {
          name: 'configure',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('configure', {}, 'actions'),
          modal: [MODAL_AGENDA_PARAMETERS],
          group: trans('management'),
          displayed: false // TODO : implement
        }, {
          name: 'import',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-upload',
          label: trans('import', {}, 'actions'),
          callback: () => props.importEvents(null, props.contextData),
          group: trans('transfer'),
          displayed: false // TODO : implement
        }, {
          name: 'export',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export', {}, 'actions'),
          target: ['apiv2_download_agenda', {workspace: get(props.contextData, 'id')}],
          group: trans('transfer'),
          displayed: false // TODO : implement
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
              if (params.view) {
                props.changeView(params.view)
              }

              // grab reference date from params
              if (params.year) {
                const newReference = moment(props.referenceDate)
                newReference.year(params.year)

                if (params.month) {
                  newReference.month(params.month - 1)
                }

                if (params.day) {
                  newReference.date(params.day)
                }

                props.changeReference(newReference)

                // load events list
                const newView = AGENDA_VIEWS[params.view || props.view]
                props.loadEvents(newView.range(newReference))
              }
            },
            render: () => {
              const CurrentView = createElement(currentView.component, {
                path: props.path,
                loaded: props.loaded,
                view: props.view,
                referenceDate: props.referenceDate,
                range: currentRange,
                previous: currentView.previous,
                next: currentView.next,

                events: props.events,
                createEvent: (event) => props.createEvent(event, props.currentUser)
              })

              return CurrentView
            }
          }
        ]}
      />
    </ToolPage>
  )
}

AgendaTool.propTypes = {
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
    'schedule'
  ]).isRequired,
  referenceDate: T.object.isRequired,// moment date object
  changeView: T.func.isRequired,
  changeReference: T.func.isRequired,

  loaded: T.bool.isRequired,
  events: T.arrayOf(T.shape({

  })).isRequired,
  createEvent: T.func.isRequired,
  loadEvents: T.func.isRequired,
  importEvents: T.func.isRequired
}

export {
  AgendaTool
}
