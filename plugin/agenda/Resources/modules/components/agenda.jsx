import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import $ from 'jquery'
import cloneDeep from 'lodash/cloneDeep'

import {url} from '#/main/app/api/router'
import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {getApiFormat} from '#/main/core/scaffolding/date'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions} from '#/plugin/agenda/actions'
import {PageContainer, PageHeader, PageActions, MoreAction} from '#/main/core/layout/page'

import {Calendar} from '#/plugin/agenda/components/calendar.jsx'
import {FilterBar} from '#/plugin/agenda/components/filter-bar'
import {MODAL_EVENT} from '#/plugin/agenda/components/modal'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_DATA_FORM} from '#/main/app/modals/form'


function arrayTrans(key) {
  if (typeof key === 'object') {
    const transWords = []
    for (let i = 0; i < key.length; i++) {
      transWords.push(trans(key[i], {}, 'agenda'))
    }

    return transWords
  }
}

function sanitize(event) {
  const data = cloneDeep(event)
  data.start = event.start.format(getApiFormat())
  data.end = event.end.format(getApiFormat())
  delete data.source

  return data
}

const form = [
  {
    title: trans('general'),
    primary: true,
    fields: [{
      name: 'title',
      type: 'string',
      label: trans('title'),
      required: true
    }, {
      name: 'description',
      type: 'string',
      label: trans('description'),
      required: true,
      options: {
        long: true
      }
    }]
  },
  {
    title: trans('properties'),
    fields: [{
      name: 'meta.task',
      type: 'boolean',
      label: trans('task'),
      required: true
    },
    {
      name: 'allDay',
      type: 'boolean',
      label: trans('all_day'),
      required: true,
      options: {
        time: true
      }
    },
    {
      name: 'start',
      type: 'date',
      label: trans('form.start', {}, 'agenda'),
      required: true,
      options: {
        time: true
      }
    },
    {
      name: 'end',
      type: 'date',
      label: trans('form.end', {}, 'agenda'),
      required: true,
      options: {
        time: true
      }
    }]
  }
]

class AgendaComponent extends Component {
  constructor(props) {
    super(props)
    this.getFetchRoute = this.getFetchRoute.bind(this)
    this.calendar = {
      header: {
        left: 'prev,next, today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
      },
      buttonText: {
        prev: trans('prev', {}, 'agenda'),
        next: trans('next', {}, 'agenda'),
        prevYear: trans('prevYear', {}, 'agenda'),
        nextYear: trans('nextYear', {}, 'agenda'),
        today: trans('today', {}, 'agenda'),
        month: trans('month_', {}, 'agenda'),
        week: trans('week', {}, 'agenda'),
        day: trans('day_', {}, 'agenda')
      },
      firstDay: 1,
      monthNames: arrayTrans(['month.january', 'month.february', 'month.march', 'month.april', 'month.may', 'month.june', 'month.july', 'month.august', 'month.september', 'month.october', 'month.november', 'month.december']),
      monthNamesShort: arrayTrans(['month.jan', 'month.feb', 'month.mar', 'month.apr', 'month.may', 'month.ju', 'month.jul', 'month.aug', 'month.sept',  'month.oct', 'month.nov', 'month.dec']),
      dayNames: arrayTrans(['day.sunday', 'day.monday', 'day.tuesday', 'day.wednesday', 'day.thursday', 'day.friday', 'day.saturday']),
      dayNamesShort: arrayTrans(['day.sun', 'day.mon', 'day.tue', 'day.wed', 'day.thu', 'day.fri', 'day.sat']),
      //This is the url which will get the events from ajax the 1st time the calendar is launched
      //aussi il faudra virer le routing.generate ici (filtrer par workspace si il y a)
      /** @global Routing */
      events: this.getFetchRoute(),
      timeFormat: 'H:mm',
      agenda: 'h:mm{ - h:mm}',
      allDayText: trans('isAllDay'),
      lazyFetching : false,
      fixedWeekCount: false,
      eventLimit: 4,
      timezone: 'local',
      eventDrop: props.onEventDrop,
      dayClick: props.onDayClick,
      eventClick:  props.onEventClick,
      eventDestroy: props.onEventDestroy,
      eventRender: props.onEventRender,
      eventResize: props.onEventResize,
      workspace: props.workspace
    }
  }

  getFetchRoute() {
    return url(['apiv2_event_list'], {filters: this.props.filters})
  }

  render() {
    return (
      <PageContainer>
        <PageHeader
          title={trans('agenda', {}, 'tools')}
        >
          <PageActions>
            <MoreAction
              actions={[
                {
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-upload',
                  label: trans('import'),
                  callback: this.props.openImportForm
                }, {
                  type: URL_BUTTON,
                  icon: 'fa fa-fw fa-download',
                  label: trans('export'),
                  target: url(['apiv2_download_agenda', {workspace: this.props.workspace.id}])
                }
              ]}
            />
          </PageActions>
        </PageHeader>

        <div className="row">
          <div className="col-md-9">
            <Calendar {...this.calendar} />
          </div>

          <FilterBar
            onChangeFiltersType={this.props.onChangeFiltersType}
            onChangeFiltersWorkspace={this.props.onChangeFiltersWorkspace}
            workspace={this.props.workspace}
            workspaces={this.props.workspaces}
            filters={this.props.filters}
          />
        </div>
      </PageContainer>
    )
  }
}

const Agenda = connect(
  state => ({
    //workspaces is for filter in desktop if the array is here
    workspaces: state.workspaces,
    workspace: state.workspace,
    filters: state.filters
  }),
  dispatch => ({
    openImportForm(workspace = null) {
      dispatch (
        modalActions.showModal(MODAL_DATA_FORM, {
          title: trans('import'),
          save: data => {
            //bad hack for $('#fullcalendar')
            dispatch(actions.import(data, workspace, $('#fullcalendar')))
          },
          sections: [
            {
              title: trans('general'),
              primary: true,
              fields: [{
                name: 'file',
                type: 'file',
                label: trans('file'),
                required: true
              }]
            }
          ]
        })
      )
    },
    onDayClick(calendarRef, workspace, date) {
      dispatch (
        modalActions.showModal(MODAL_DATA_FORM, {
          title: 'event',
          save: event => {
            dispatch(actions.create(event, workspace, calendarRef))
          },
          sections: form,
          data: {
            start: date.format(getApiFormat()),
            end: date.add(1, 'days').format(getApiFormat())
          }
        })
      )
    },
    onEventDrop(calendarRef, event) {
      dispatch(actions.update(sanitize(event), calendarRef))
    },
    onEventClick(calendarRef, event) {
      dispatch (
        modalActions.showModal(MODAL_EVENT, {
          event: sanitize(event),
          onDelete: () => {
            dispatch(
              modalActions.showModal(MODAL_CONFIRM, {
                title: trans('remove_event', {}, 'agenda') + ': ' + event.title,
                question: trans('remove_event_confirm', {}, 'agenda'),
                confirmButtonText: trans('delete'),
                dangerous: true,
                handleConfirm: () => {
                  dispatch(actions.delete(event, calendarRef))
                }
              })
            )
          },
          onForm: () => {
            dispatch (
              modalActions.showModal(MODAL_DATA_FORM, {
                title: 'event',
                save: event => {
                  dispatch(actions.update(event, calendarRef))
                },
                sections: form,
                data: sanitize(event)
              })
            )
          }
        })
      )
    },
    onEventRender(calendarRef, event, $element) {
      //we need timezone here.
      //event.start.utcOffset(2)

      if (event.editable) {
        $element.addClass('fc-draggable')
      }

      if (event.meta.task) {
        const eventContent =  $element.find('.fc-content')
        // Remove the date
        eventContent.find('.fc-time').remove()
        eventContent.prepend('<span class="task fa" data-event-id="' + event.id + '"></span>')

        $element.css({
          'background-color': 'rgb(144, 32, 32)',
          'border-color': 'rgb(144, 32, 32)'
        })

        // Add the checkbox if the task is not done or the check symbol if the task is done
        const checkbox = eventContent.find('.task')

        if (event.isTaskDone) {
          checkbox.addClass('fa-check-square-o')
          checkbox.next().css('text-decoration', 'line-through')
        } else {
          checkbox.addClass('fa-square-o')
        }
      }
    },
    onEventResize(calendarRef, event) {
      const data = cloneDeep(event)
      data.start = event.start.format(getApiFormat())
      data.end = event.end.format(getApiFormat())
      delete data.source
      dispatch(actions.update(data, calendarRef))
    },
    onChangeFiltersType(filters, allFilters) {
      dispatch(actions.updateFilterType(filters))
      //otherwise it lags behind
      let newFilters = cloneDeep(allFilters)
      newFilters.types = filters
      $('#fullcalendar').fullCalendar('removeEventSources')
      $('#fullcalendar').fullCalendar('addEventSource', url(['apiv2_event_list'], {filters: newFilters}))
      $('#fullcalendar').fullCalendar('refetchEvents')
    },
    onChangeFiltersWorkspace(filters, allFilters) {
      dispatch(actions.updateFilterWorkspace(filters))
      //otherwise it lags behind
      let newFilters = cloneDeep(allFilters)
      newFilters.workspaces = filters
      $('#fullcalendar').fullCalendar('removeEventSources')
      $('#fullcalendar').fullCalendar('addEventSource', url(['apiv2_event_list'], {filters: newFilters}))
      $('#fullcalendar').fullCalendar('refetchEvents')
    }
  })
)(AgendaComponent)

AgendaComponent.propTypes = {
  onEventDrop: T.func.isRequired,
  onDayClick: T.func.isRequired,
  onEventClick: T.func.isRequired,
  onEventDestroy: T.func.isRequired,
  onEventRender: T.func.isRequired,
  onEventResize: T.func.isRequired,
  onEventResizeStart: T.func.isRequired,
  openImportForm: T.func.isRequired,
  onChangeFiltersWorkspace: T.func.isRequired,
  onChangeFiltersType: T.func.isRequired,
  workspace: T.object,
  workspaces: T.object.isRequired,
  filters: T.object.isRequired
}

export {
  Agenda
}
