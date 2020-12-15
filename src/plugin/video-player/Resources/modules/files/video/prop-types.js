import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {File} from '#/main/core/files/prop-types'

const Track = {
  propTypes: {
    id: T.string.isRequired,
    autoId: T.number,
    meta: T.shape({
      label: T.string,
      lang: T.string,
      kind: T.string,
      default: T.bool
    })
  }
}

const Video = merge({}, File, {
  propTypes: {
    tracks: T.arrayOf(T.shape(
      Track.propTypes
    ))
  }
})

export {
  Track,
  Video
}
