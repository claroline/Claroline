import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api/router'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {PageContainer, PageHeader, PageActions, MoreAction} from '#/main/core/layout/page'

import {Calendar} from '#/plugin/agenda/tools/agenda/components/calendar'
import {FilterBar} from '#/plugin/agenda/tools/agenda/components/filter-bar'

function arrayTrans(key) {
  if (typeof key === 'object') {
    const transWords = []
    for (let i = 0; i < key.length; i++) {
      transWords.push(trans(key[i], {}, 'agenda'))
    }

    return transWords
  }
}

class AgendaTool extends Component {
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
      slotLabelFormat: 'H:mm',
      timeFormat: 'H:mm',
      agenda: 'h:mm{ - h:mm}',
      allDayText: trans('isAllDay', {}, 'agenda'),
      lazyFetching : false,
      fixedWeekCount: false,
      eventLimit: 4,
      timezone: 'local',
      eventDrop: props.onEventDrop,
      dayClick: props.onDayClick,
      eventClick:  props.onEventClick,
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

AgendaTool.propTypes = {
  onEventDrop: T.func.isRequired,
  onDayClick: T.func.isRequired,
  onEventClick: T.func.isRequired,
  onEventRender: T.func.isRequired,
  onEventResize: T.func.isRequired,
  openImportForm: T.func.isRequired,
  onChangeFiltersWorkspace: T.func.isRequired,
  onChangeFiltersType: T.func.isRequired,
  workspace: T.object,
  workspaces: T.object.isRequired,
  filters: T.object.isRequired
}

export {
  AgendaTool
}
