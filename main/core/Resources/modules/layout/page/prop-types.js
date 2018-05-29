import {PropTypes as T} from 'prop-types'

const Page = {
  propTypes: {
    className: T.string,

    /**
     * Is the page displayed in full screen ?
     */
    fullscreen: T.bool,
    embedded: T.bool
  },
  defaultProps: {
    fullscreen: false,
    embedded: false
  }
}

export {
  Page
}
