import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {DatePicker} from '#/main/core/layout/form/components/field/date-picker.jsx'
import moment from 'moment'

class DateSearch extends Component {
  constructor(props) {
    super(props)
    this.state = {open: true}
  }

  openPicker() {
    this.setState({open: true})
  }

  closePicker() {
    this.setState({open: false})
  }

  render() {
    return(
      <span className="date-filter">
        {this.props.isValid &&
          <span className="available-filter-value">{this.props.search}</span>
        }
        &nbsp;
        <DatePicker
          className="input-hide"
          showCalendarButton={true}
          onChange={date => this.props.updateSearch(date)}
          minDate={moment.utc('1970')}
          name="filter-date"
          open={this.state.open}
        >
        </DatePicker>
      </span>
    )
  }
}

DateSearch.propTypes = {
  search: T.string.isRequired,
  isValid: T.bool.isRequired,
  updateSearch: T.func.isRequired
}

export {DateSearch}
