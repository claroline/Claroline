import {PropTypes as T} from 'prop-types'

// TODO : use specific action types

/**
 * Definition of the action.
 */
const Action = {
  propTypes: {
    // an unique identifier for the action
    // most of the time we can generate it from label (that's why it's optional)
    // but it's not sufficient, for actions on data collection (same action names for each items)
    id: T.string,
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
      type: T.oneOf(['default', 'primary', 'danger', 'warning']),
      value: T.oneOfType([T.string, T.number]).isRequired
    }),
    label: T.string.isRequired,
    group: T.string,
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

  AsyncAction,
  CallbackAction,
  DownloadAction,
  EmailAction,
  LinkAction,
  MenuAction,
  ModalAction,
  UrlAction
}