import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/action/constants'

/**
 * Definition of the action.
 */
const Action = {
  propTypes: {
    /**
     * An unique identifier for the action
     *
     * Most of the time we can generate it from label (that's why it's optional)
     * but it's not sufficient, for actions on data collection (same action names for each items)
     *
     * @type {string}
     */
    id: T.string,
    name: T.string,
    type: T.string.isRequired,
    icon: T.oneOfType([T.string, T.node]),
    subscript: T.shape({
      type: T.oneOf(['text', 'label']),
      status: T.oneOf(['default', 'primary', 'danger', 'warning']),
      value: T.node.isRequired
    }),

    /**
     * The display label of the action.
     *
     * @type {string}
     */
    label: T.node.isRequired,
    group: T.string,

    /**
     * The scope of the action.
     *
     * @type {string}
     */
    scope: T.arrayOf(
      T.oneOf(constants.ACTION_SCOPES)
    ),
    disabled: T.bool,
    displayed: T.bool,
    primary: T.bool,
    dangerous: T.bool,
    active: T.bool,

    /**
     * If provided, the action will request a user confirmation before executing the action.
     *
     * @type {object}
     */
    confirm: T.oneOfType([
      T.bool,
      T.shape({
        title: T.string,
        subtitle: T.string,
        message: T.string,
        button: T.string
      })
    ])
  },
  defaultProps: {
    disabled: false,
    displayed: true,
    primary: false,
    dangerous: false
  }
}

const PromisedAction = {
  propTypes: {
    then: T.func.isRequired,
    catch: T.func.isRequired
  }
}

const Toolbar = {
  propTypes: {
    id: T.string,

    /**
     * The base class of the toolbar (it's used to generate classNames which can be used for styling).
     */
    name: T.string,

    /**
     * The additional classes to add to the toolbar.
     *
     * @type {string}
     */
    className: T.string,

    /**
     * Disables all the actions of the toolbar at once.
     *
     * @type {bool}
     */
    disabled: T.bool,

    /**
     * The base class for buttons.
     *
     * @type {string}
     */
    buttonName: T.string,

    size: T.oneOf(['xs', 'sm', 'lg']),

    /**
     * The toolbar display configuration as a string.
     *
     * It uses the same format than tinyMCE.
     * Example : 'edit publish | like'.
     *
     * @type {string}
     */
    toolbar: T.string,

    /**
     * The scope of the toolbar (to know which actions to display)
     *
     * @type {string}
     */
    scope: T.oneOf(constants.ACTION_SCOPES),

    /**
     * The position for button tooltips.
     *
     * @type {string}
     */
    tooltip: T.oneOf(['left', 'top', 'right', 'bottom']),

    /**
     * The list of actions available in the toolbar.
     */
    actions: T.oneOfType([
      // a regular array of actions
      T.arrayOf(T.shape(
        Action.propTypes
      )),
      // a promise that will resolve a list of actions
      T.shape(
        PromisedAction.propTypes
      )
    ]).isRequired,
    onClick: T.func
  },
  defaultProps: {
    className: 'toolbar',
    disabled: false
  }
}

export {
  Action,
  PromisedAction,
  Toolbar
}
