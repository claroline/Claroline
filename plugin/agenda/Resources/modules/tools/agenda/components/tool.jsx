import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {now} from '#/main/app/intl/date'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {calendarUrl} from '#/plugin/agenda/tools/agenda/utils'
import {AGENDA_VIEWS} from '#/plugin/agenda/tools/agenda/views'
import {MODAL_AGENDA_PARAMETERS} from '#/plugin/agenda/tools/agenda/modals/parameters'
import {MODAL_EVENT} from '#/plugin/agenda/tools/agenda/modals/event'

const AgendaTool = props => {
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
          target: calendarUrl(props.path, props.view, currentView.previous(props.referenceDate))
        }, {
          name: 'next',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-chevron-right',
          label: trans('next'),
          target: calendarUrl(props.path, props.view, currentView.next(props.referenceDate))
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
              target: calendarUrl(props.path, viewName, props.referenceDate)
            }))
          }
        }, {
          name: 'today',
          type: LINK_BUTTON,
          icon: <span>{trans('today')}</span>,
          label: trans('today'),
          target: calendarUrl(props.path, props.view, now())
        }, {
          name: 'add',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add-event', {}, 'actions'),
          modal: [MODAL_EVENT, {}],
          primary: true
        }, {
          name: 'configure',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('configure', {}, 'actions'),
          modal: [MODAL_AGENDA_PARAMETERS],
          group: trans('management')
        }, {
          name: 'import',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-upload',
          label: trans('import', {}, 'actions'),
          callback: () => props.import(null, props.contextData),
          group: trans('transfer')
        }, {
          name: 'export',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export', {}, 'actions'),
          target: ['apiv2_download_agenda', {workspace: get(props.contextData, 'id')}],
          group: trans('transfer')
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
              }
            },
            render: () => {
              const CurrentView = createElement(currentView.component, {
                path: props.path,
                referenceDate: props.referenceDate,
                range: currentRange
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

  import: T.func.isRequired
}

export {
  AgendaTool
}
