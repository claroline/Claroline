import {PropTypes as T} from 'prop-types'

const Video = {
  propTypes: {
    id: T.string,
    url: T.string,
    embeddedUrl: T.string,
    timecodeStart: T.string,
    timecodeEnd: T.string,
    autoplay: T.bool,
    looping: T.bool,
    controls: T.bool,
    peertubeLink: T.bool
  },
  defaultProps: {

  }
}

export {
  Video
}
