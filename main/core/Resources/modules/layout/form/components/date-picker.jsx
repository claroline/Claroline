import React, {PropTypes as T, Component} from 'react'
import DatePicker from 'react-datepicker'
import moment from 'moment'
import 'react-datepicker/dist/react-datepicker.css'

const locale = getLocale()

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
          selected={this.props.value ? moment.utc(this.props.value) : null}
          onChange={date => this.props.onChange(moment.utc(date).format(locale))}
          ref={(c) => this._calendar = c}
          {...this.props}
        >
        </DatePicker>
      </span>
    )
  }
}

Date.propTypes = {
  id: T.string,
  name: T.string.isRequired,
  onChange: T.func.isRequired,
  value: T.string,
  className: T.string,
  minDate: T.object,
  showCalendarButton: T.bool.isRequired
}

Date.defaultProps = {
  className: 'form-control',
  minDate: moment.utc(),
  showCalendarButton: false
}

// tmp: current way of retrieving locale...
function getLocale() {
  const locale = document.querySelector('#homeLocale')

  if (locale) {
    return locale.innerHTML.trim()
  }

  return 'en'
}

export {Date as DatePicker}
