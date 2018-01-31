import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'

const DatetimeGroup = props =>
  <FormGroup
    {...props}
  >
    <Datetime
      closeOnSelect={props.closeOnSelect}
      dateFormat={props.dateFormat}
      timeFormat={props.timeFormat}
      locale={props.locale}
      utc={props.utc}
      defaultValue={props.defaultValue}
      onChange={props.onChange}
    />
  </FormGroup>

DatetimeGroup.propTypes = {
  controlId: T.string.isRequired,
  closeOnSelect: T.bool,
  dateFormat: T.bool,
  timeFormat: T.bool,
  locale: T.string,
  utc: T.bool,
  defaultValue: T.string,
  onChange: T.func.isRequired
}

DatetimeGroup.defaultProps = {
  closeOnSelect: true,
  dateFormat: true,
  timeFormat: true,
  locale: 'fr',
  utc: false,
  defaultValue: '',
  onChange: () => {}
}

export {
  DatetimeGroup
}
