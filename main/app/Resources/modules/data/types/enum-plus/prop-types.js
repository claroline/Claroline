import {PropTypes as T} from 'prop-types'

const EnumPlusOptions = {
  propTypes: {
    choices: T.object.isRequired,
    multiple: T.bool,
    noEmpty: T.bool
  },
  defaultProps: {
    multiple: false,
    noEmpty: false
  }
}

export {
  EnumPlusOptions
}
