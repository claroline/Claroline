import {PropTypes as T} from 'prop-types'

const Gauge = {
  propTypes: {
    className: T.string,

    /**
     * The type of the Gauge (to apply correct color scheme).
     */
    type: T.oneOf(['primary', 'success', 'warning', 'danger', 'info', 'user', 'custom']),

    /**
     * The current value.
     */
    value: T.number,
    displayValue: T.func,

    /**
     * The available width for the Gauge.
     */
    width: T.number,

    /**
     * The available height for the Gauge.
     */
    height: T.number,
    color: T.string,
    preFilled: T.bool
  },
  defaultProps: {
    type: 'primary',
    width: 100,
    height: 100,
    preFilled: false
  }
}

export {
  Gauge
}
