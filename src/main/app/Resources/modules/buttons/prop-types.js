import {PropTypes as T} from 'prop-types'

const Button = {
  propTypes: {
    id: T.string,
    className: T.string,
    size: T.oneOf(['sm', 'lg']),
    variant: T.string,
    children: T.node.isRequired,
    disabled: T.bool,
    active: T.bool,
    tabIndex: T.number,

    /**
     * If provided, the button will request a user confirmation before executing the action.
     *
     * @type {object}
     */
    confirm: T.oneOfType([
      // display a generic confirmation message
      T.bool,
      // display a custom confirmation message
      T.string,
      // full configuration of the confirmation modale
      T.shape({
        additional: T.string,
        message: T.string,
        button: T.string,
        items: T.arrayOf(T.shape({
          id: T.string.isRequired,
          name: T.string.isRequired,
          thumbnail: T.string
        }))
      })
    ])
  },
  defaultProps: {
    disabled: false
  }
}

export {
  Button
}
