import {PropTypes as T} from 'prop-types'

const Video = {
  propTypes: {
    videoId: T.string,
    url: T.string,
    timecodeStart: T.number,
    timecodeEnd: T.number,
    autoplay: T.bool,
    looping: T.bool,
    controls: T.bool,
    resume: T.bool
  },
  defaultProps: {
  }
}

export {
  Video
}
