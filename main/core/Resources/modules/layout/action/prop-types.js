import {PropTypes as T} from 'prop-types'

/**
 * Definition af an UI action.
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

    /**
     * The icon representing the action.
     * NB. It only accepts font based icons.
     *
     * @type {string}
     */
    icon: T.string,

    /**
     * The action itself (an URL or a function to call).
     *
     * @type {string|function}
     */
    action: T.oneOfType([T.string, T.func]).isRequired, // todo should also accept url array

    /**
     * Is the action displayed ?
     *
     * @type {bool}
     */
    displayed: T.bool,

    /**
     * Is the action disabled ?
     *
     * @type {bool}
     */
    disabled: T.bool,

    /**
     * Is the action primary ?
     *
     * @type {bool}
     */
    primary: T.bool,

    /**
     * Is the action dangerous ?
     *
     * @type {bool}
     */
    dangerous: T.bool
  },
  defaultProps: {
    icon: 'fa fa-fw fa-circle',
    displayed: true,
    disabled: false,
    primary: false,
    dangerous: false
  }
}

export {
  Action
}
