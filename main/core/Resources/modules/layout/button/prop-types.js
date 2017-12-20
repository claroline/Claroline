import {PropTypes as T} from 'prop-types'

// todo move in #/main/core/layout/action

/**
 * Definition af an action.
 *
 * @type {object}
 */
const Action = {
  propTypes: {
    /**
     * The label associated to the action.
     *
     * @type {string}
     */
    label: T.string.isRequired,

    icon: T.string,

    /**
     * The action itself (an URL or a function to call).
     *
     * @type {string|function}
     */
    action: T.oneOfType([T.string, T.func]).isRequired, // todo should also accept url array

    /**
     * Is the action disabled ?
     */
    disabled: T.bool,

    /**
     * Is the action primary ?
     */
    primary: T.bool,

    /**
     * Is the action dangerous ?
     */
    dangerous: T.bool
  },
  defaultProps: {
    icon: 'fa fa-fw fa-circle',
    disabled: false,
    primary: false,
    dangerous: false
  }
}

export {
  Action
}
