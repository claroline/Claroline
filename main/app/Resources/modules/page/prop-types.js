import {PropTypes as T} from 'prop-types'

import {Action} from '#/main/app/action/prop-types'

const Page = {
  propTypes: {
    className: T.string, // todo maybe not
    title: T.string.isRequired,
    subtitle: T.string,
    poster: T.string,
    embedded: T.bool,
    fullscreen: T.bool,
    toolbar: T.string,
    actions: T.arrayOf(T.shape(
      Action.propTypes
    )),
    sections: T.arrayOf(T.shape({

    }))
  },
  defaultProps: {
    embedded: false,
    fullscreen: false,
    actions: []
  }
}

export {
  Page
}
