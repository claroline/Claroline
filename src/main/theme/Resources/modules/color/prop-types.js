import {PropTypes as T} from 'prop-types'

const ColorChart = {
  propTypes: {
    /**
     * The selected color in the color chart.
     *
     * @type {string}
     */
    selected: T.string,

    showCurrent: T.bool,

    /**
     * A callback executed when the selected color changes.
     */
    onChange: T.func.isRequired
  },
  defaultProps: {
    selected: '',
    showCurrent: true
  }
}

export {
  ColorChart
}
