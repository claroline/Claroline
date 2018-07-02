import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {actions as listActions} from '#/main/core/data/list/actions'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {Calendar} from '#/main/core/layout/calendar/components/calendar.jsx'
import {trans} from '#/main/core/translation'

const BlogCalendarComponent = props =>
  <div key='redactors' className="panel panel-default">
    <div className="panel-heading"><h2 className="panel-title">{trans('calendar', {}, 'icap_blog')}</h2></div>
    <div className="panel-body calendar">
      <Calendar
        selected={props.calendarSelectedDate}
        onChange={props.searchByDate}
        time={false}
      />
    </div>
  </div>
    
BlogCalendarComponent.propTypes = {
  calendarSelectedDate: T.string,
  searchByDate: T.func.isRequired
}

const BlogCalendar = connect(
  state => ({
    calendarSelectedDate: state.calendarSelectedDate
  }),
  dispatch => ({
    searchByDate: (date) => {
      dispatch(listActions.addFilter('posts', 'publicationDate', date))
      dispatch(postActions.initDataList())
    }
  })
)(BlogCalendarComponent)

export {BlogCalendar}