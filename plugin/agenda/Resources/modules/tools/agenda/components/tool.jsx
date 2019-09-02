import React, {Component, createElement} from 'react'
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

class AgendaTool extends Component {
  constructor(props) {
    super(props)

    const currentView = AGENDA_VIEWS[this.props.view]
    this.state = {
      currentView: currentView,
      currentRange: currentView.range(this.props.referenceDate)
    }
  }

  componentDidMount() {
    if (!this.props.loaded) {
      this.props.loadEvents(this.state.currentRange)
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.view !== this.props.view || prevProps.referenceDate !== this.props.referenceDate) {
      const currentView = AGENDA_VIEWS[this.props.view]
      this.setState({
        currentView: currentView,
        currentRange: currentView.range(this.props.referenceDate)
      })
    }

    if (!this.props.loaded && prevProps.loaded !== this.props.loaded) {
      this.props.loadEvents(this.state.currentRange)
    }
  }

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
            callback: () => this.props.createEvent({
              start: now(false)
            }, this.props.currentUser),
            displayed: !isEmpty(this.props.currentUser),
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
            callback: () => this.props.importEvents(null, this.props.contextData),
            group: trans('transfer')
          }, {
            name: 'export',
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-download',
            label: trans('export', {}, 'actions'),
            target: ['apiv2_download_agenda', {workspace: get(this.props.contextData, 'id')}],
            group: trans('transfer')
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
                if (params.view) {
                  this.props.changeView(params.view)
                }

                // grab reference date from params
                if (params.year) {
                  const newReference = moment(this.props.referenceDate)
                  newReference.year(params.year)

                  if (params.month) {
                    newReference.month(params.month - 1)
                  }

                  if (params.day) {
                    newReference.date(params.day)
                  }

                  this.props.changeReference(newReference)
                }
              },
              render: () => {
                const CurrentView = createElement(currentView.component, {
                  path: this.props.path,
                  loaded: this.props.loaded,
                  view: this.props.view,
                  referenceDate: this.props.referenceDate,
                  range: currentRange,
                  previous: currentView.previous,
                  next: currentView.next,

                  events: this.props.events,
                  createEvent: (event) => this.props.createEvent(event, this.props.currentUser)
                })

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
