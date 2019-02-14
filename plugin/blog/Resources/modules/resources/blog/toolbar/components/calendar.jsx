import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'
import {selectors} from '#/plugin/blog/resources/blog/store'

const BlogCalendarComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading">
      <h2 className="panel-title">{trans('calendar')}</h2>
    </div>

    <Calendar
      selected={props.calendarSelectedDate}
      onChange={props.searchByDate}
      time={false}
      showCurrent={false}
    />
  </div>

BlogCalendarComponent.propTypes = {
  calendarSelectedDate: T.string,
  searchByDate: T.func.isRequired
}

const BlogCalendar = connect(
  state => ({
    calendarSelectedDate: selectors.calendarSelectedDate(state)
  }),
  dispatch => ({
    searchByDate: (date) => {
      dispatch(listActions.addFilter(selectors.STORE_NAME+'.posts', 'publicationDate', date))
      dispatch(postActions.initDataList())
    }
  })
)(BlogCalendarComponent)

export {BlogCalendar}
