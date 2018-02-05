import {PropTypes as T} from 'prop-types'

const CalendarView = {
  propTypes: {
    now: T.object.isRequired,
    selected: T.object,

    /**
     * The current displayed range.
     */
    currentRange: T.arrayOf(
      T.object
    ).isRequired,

    /**
     * The calendar date boundaries.
     */
    calendarRange: T.arrayOf(
      T.object
    ).isRequired,

    // methods
    changeView: T.func.isRequired,
    update: T.func.isRequired
  }
}

const Calendar = {
  propTypes: {
    /**
     * The selected date in the calendar.
     *
     * @type {string}
     */
    selected: T.string,

    /**
     * A callback executed when the selected date changes.
     */
    onChange: T.func.isRequired,

    /**
     * The minimum selectable date in the calendar.
     *
     * @type {string}
     */
    minDate: T.string,

    /**
     * The maximum selectable date in the calendar.
     *
     * @type {string}
     */
    maxDate: T.string,

    /**
     * Does the calendar also embed time management ?
     *
     * @type {boolean}
     */
    time: T.bool,

    /**
     * The minimum selectable time.
     * Example: 06:30.
     *
     * @type {string}
     */
    minTime: T.string,

    /**
     * The maximum selectable time.
     * Example: 18:30.
     *
     * @type {string}
     */
    maxTime: T.string
  },
  defaultProps: {
    selected: '',
    // date configuration
    minDate: '1900-01-01',
    maxDate: '2099-12-31',

    // time configuration
    time: false,
    minTime: '00:00',
    maxTime: '23:59'
  }
}

export {
  CalendarView,
  Calendar
}
