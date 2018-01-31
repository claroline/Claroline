import {describeComponent} from '#/main/core/scaffolding/tests'

import {Date as DateField} from './date.jsx'

describeComponent('Date', DateField,
  // required props
  [
    'id',
    'onChange'
  ],
  // invalid props
  {
    id: 123,
    value: false,
    onChange: 'foo',
    minDate: true,
    maxDate: true,
    time: 'bar',
    minTime: true,
    maxTime: true
  },
  // valid props
  {
    id: '123',
    value: '2012-09-01',
    onChange: () => {},
    minDate: '2000-01-01',
    maxDate: '2050-01-01',
    time: true,
    minTime: '06:30',
    maxTime: '22:00'
  }
)
