import {connect} from 'react-redux'
import $ from 'jquery'
import cloneDeep from 'lodash/cloneDeep'

import {url} from '#/main/app/api/router'
import {trans} from '#/main/app/intl/translation'
import {getApiFormat} from '#/main/app/intl/date'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions} from '#/plugin/agenda/tools/agenda/store'

import {AgendaTool as AgendaToolComponent} from '#/plugin/agenda/tools/agenda/components/tool'
import {MODAL_EVENT} from '#/plugin/agenda/tools/agenda/modals/event'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_DATA_FORM} from '#/main/app/modals/form'

function sanitize(event) {
  const data = cloneDeep(event)
  data.start = event.start ? event.start.format(getApiFormat()): null
  data.end = event.end ? event.end.format(getApiFormat()): null
  delete data.source

  return data
}

const form = [
  {
    title: trans('general'),
    primary: true,
    fields: [
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        required: true
      }, {
        name: 'description',
        type: 'html',
        label: trans('description'),
        required: true
      }
    ]
  }, {
    title: trans('properties'),
    fields: [
      {
        name: 'meta.task',
        type: 'boolean',
        label: trans('task'),
        required: true
      }, {
        name: 'allDay',
        type: 'boolean',
        label: trans('all_day', {}, 'agenda'),
        required: true,
        options: {
          time: true
        }
      }, {
        name: 'color',
        type: 'color',
        label: trans('color', {}, 'platform'),
        required: false
      }, {
        name: 'start',
        type: 'date',
        label: trans('form.start', {}, 'agenda'),
        required: true,
        options: {
          time: true
        }
      }, {
        name: 'end',
        type: 'date',
        label: trans('form.end', {}, 'agenda'),
        required: true,
        options: {
          time: true
        }
      }
    ]
  }
]

const AgendaTool = connect(
  (state) => ({
    //workspaces is for filter in desktop if the array is here
    workspaces: state.workspaces,
    workspace: state.workspace,
    filters: state.filters
  }),
  (dispatch) => ({
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
          title: trans('event', {}, 'agenda'),
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
)(AgendaToolComponent)

export {
  AgendaTool
}
