import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {now} from '#/main/app/intl/date'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {route} from '#/plugin/agenda/tools/agenda/routing'
import {AGENDA_VIEWS} from '#/plugin/agenda/tools/agenda/views'
import {MODAL_AGENDA_PARAMETERS} from '#/plugin/agenda/tools/agenda/modals/parameters'
import {constants} from '#/plugin/agenda/event/constants'
import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'

class CalendarComponent extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.loadEvents()
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.loaded !== this.props.loaded && !this.props.loaded) {
      this.loadEvents()
    }
  }

  loadEvents() {
    // load events list
    const reference = moment(this.props.referenceDate)
    const view = AGENDA_VIEWS[this.props.view]

    this.props.loadEvents(view.range(reference))
  }

  render() {
    return this.props.children
  }
}

const Calendar = withRouter(CalendarComponent)

class AgendaTool extends Component {
  render() {
    const currentView = AGENDA_VIEWS[this.props.view]
    const currentRange = currentView.range(this.props.referenceDate)

    return (
      <ToolPage
        subtitle={currentView.display(this.props.referenceDate)}
        toolbar="add | previous range next | today | more"
        actions={[
          {
            name: 'previous',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-chevron-left',
            label: trans('previous'),
            target: route(this.props.path, this.props.view, currentView.previous(this.props.referenceDate))
          }, {
            name: 'next',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-chevron-right',
            label: trans('next'),
            target: route(this.props.path, this.props.view, currentView.next(this.props.referenceDate))
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
                target: route(this.props.path, viewName, this.props.referenceDate)
              }))
            }
          }, {
            name: 'today',
            type: LINK_BUTTON,
            icon: <span>{trans('today')}</span>,
            label: trans('today'),
            target: route(this.props.path, this.props.view, now())
          }, {
            name: 'add',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add-event', {}, 'actions'),
            callback: () => this.props.create({
              start: now(false)
            }, this.props.contextData, this.props.currentUser),
            displayed: !isEmpty(this.props.currentUser),
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
            callback: () => this.props.import(null, this.props.contextData),
            group: trans('transfer'),
            displayed: false // TODO : implement
          }, {
            name: 'export',
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-download',
            label: trans('export', {}, 'actions'),
            target: ['apiv2_download_agenda', {workspace: get(this.props.contextData, 'id')}],
            group: trans('transfer'),
            displayed: false // TODO : implement
          }
        ]}
      >
        <Routes
          path={this.props.path}
          routes={[
            {
              path: '/:view?/:year?/:month?/:day?',
              onEnter: (params = {}) => {
                // grab view from params
                let newView = this.props.view
                if (params.view) {
                  newView = params.view
                }

                // grab reference date from params
                const newReference = moment(this.props.referenceDate)
                if (params.year) {
                  newReference.year(params.year)

                  if (params.month) {
                    newReference.month(params.month - 1)
                  }

                  if (params.day) {
                    newReference.date(params.day)
                  }
                }

                this.props.changeView(newView, newReference)
              },
              render: () => {
                const CurrentView = (
                  <Calendar
                    loaded={this.props.loaded}
                    referenceDate={this.props.referenceDate}
                    view={this.props.view}
                    loadEvents={this.props.load}
                  >
                    {createElement(currentView.component, {
                      path: this.props.path,
                      loaded: this.props.loaded,
                      view: this.props.view,
                      referenceDate: this.props.referenceDate,
                      range: currentRange,
                      previous: currentView.previous,
                      next: currentView.next,
                      create: (event) => this.props.create(event, this.props.contextData, this.props.currentUser),
                      events: this.props.events,
                      eventActions: (event) => [
                        {
                          name: 'mark-done',
                          type: CALLBACK_BUTTON,
                          label: trans('mark-as-done', {}, 'actions'),
                          callback: () => this.props.markDone(event),
                          displayed: constants.EVENT_TYPE_TASK === event.meta.type && !event.meta.done
                        }, {
                          name: 'mark-todo',
                          type: CALLBACK_BUTTON,
                          label: trans('mark-as-todo', {}, 'actions'),
                          callback: () => this.props.markTodo(event),
                          displayed: constants.EVENT_TYPE_TASK === event.meta.type && event.meta.done
                        }, {
                          name: 'edit',
                          type: MODAL_BUTTON,
                          label: trans('edit', {}, 'actions'),
                          modal: [MODAL_EVENT_PARAMETERS, {
                            event: event,
                            onSave: this.props.update
                          }],
                          displayed: event.permissions.edit
                        }, {
                          name: 'delete',
                          type: CALLBACK_BUTTON,
                          label: trans('delete', {}, 'actions'),
                          callback: () => this.props.delete(event),
                          dangerous: true,
                          displayed: event.permissions.edit
                        }
                      ]
                    })}
                  </Calendar>
                )

                return CurrentView
              }
            }
          ]}
        />
      </ToolPage>
    )
  }
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

  loaded: T.bool.isRequired,
  events: T.arrayOf(T.shape({

  })).isRequired,
  load: T.func.isRequired,
  create: T.func.isRequired,
  update: T.func.isRequired,
  delete: T.func.isRequired,
  markDone: T.func.isRequired,
  markTodo: T.func.isRequired,
  import: T.func.isRequired
}

export {
  AgendaTool
}
