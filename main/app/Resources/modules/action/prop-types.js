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
    type: T.oneOf([
      'async',
      'callback',
      'download',
      'email',
      'link',
      'menu',
      'modal',
      'url'
    ]).isRequired,
    icon: T.string,
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
    label: T.string.isRequired,
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
    confirm: T.shape({
      title: T.string,
      subtitle: T.string,
      message: T.string,
      button: T.string
    })
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
     *
     * @type {string}
     */
    className: T.string,

    /**
     * The base class for buttons.
     *
     * @type {string}
     */
    buttonName: T.string,

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
    ]).isRequired
  },
  defaultProps: {
    className: 'toolbar',
    tooltip: 'bottom'
  }
}

// TODO : use specific action types

const AsyncAction = {
  propTypes: {
    target: T.oneOfType([
      T.string, // standard URL string
      T.array // symfony route array
    ]).isRequired,
    before: T.func,
    success: T.func,
    error: T.func
  },
  defaultProps: {}
}

const CallbackAction = {
  propTypes: {
    callback: T.func.isRequired
  },
  defaultProps: {}
}

const DownloadAction = {
  propTypes: {
    file: T.shape({

    }).isRequired
  },
  defaultProps: {}
}

const EmailAction = {
  propTypes: {
    email: T.string.isRequired
  },
  defaultProps: {}
}

const LinkAction = {
  propTypes: {
    target: T.string.isRequired,
    exact: T.bool
  },
  defaultProps: {
    exact: false
  }
}

const MenuAction = {
  propTypes: {
    menu: T.shape({
      label: T.string,
      position: T.oneOf(['top', 'bottom']),
      align: T.oneOf(['left', 'right']),
      items: T.arrayOf(T.shape(
        Action.propTypes
      )).isRequired
    }).isRequired
  },
  defaultProps: {
    position: 'bottom',
    align: 'left'
  }
}

const ModalAction = {
  propTypes: {
    modal: T.arrayOf([
      T.string, // the name of the modal
      T.object // the props of the modal
    ]).isRequired
  },
  defaultProps: {}
}

const PopoverAction = {
  propTypes: {
    popover: T.shape({
      className: T.string,
      label: T.string,
      position: T.oneOf(['top', 'bottom', 'left', 'right']),
      content: T.node.isRequired
    }).isRequired
  }
}

const UrlAction = {
  propTypes: {
    target: T.arrayOf([
      T.string, // the name of the modal
      T.object // the props of the modal
    ]).isRequired
  },
  defaultProps: {}
}

export {
  Action,
  PromisedAction,
  Toolbar,

  AsyncAction,
  CallbackAction,
  DownloadAction,
  EmailAction,
  LinkAction,
  MenuAction,
  ModalAction,
  PopoverAction,
  UrlAction
}