import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import DatePicker from 'react-datepicker'
import moment from 'moment'
import 'react-datepicker/dist/react-datepicker.css'

class Date extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <span>
        {this.props.showCalendarButton &&
          <button className="btn btn-sm btn-filter" onClick={() => this._calendar.setOpen(true)}>
            <span className="fa fa-fw fa-calendar"/>
          </button>
        }
        <DatePicker
          {...this.props}
          selected={this.props.value ? moment.utc(this.props.value) : null}
          onChange={date => this.props.onChange(moment.utc(date).toISOString())}
          ref={(c) => this._calendar = c}
        />
      </span>
    )
  }
}

Date.propTypes = {
  id: T.string,
  onChange: T.func.isRequired,
  value: T.string,
  className: T.string,
  minDate: T.object,
  showCalendarButton: T.bool.isRequired,
  disabled: T.bool.isRequired
}

Date.defaultProps = {
  className: 'form-control',
  minDate: moment.utc(),
  showCalendarButton: false,
  disabled: false
}

export {
  Date as DatePicker
}
