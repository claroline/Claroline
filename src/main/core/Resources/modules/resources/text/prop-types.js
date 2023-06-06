import {PropTypes as T} from 'prop-types'

const Text = {
  propTypes: {
    id: T.string,
    content: T.string,
    meta: T.shape({
      version: T.number
    })
  }
}

export {
  Text
}