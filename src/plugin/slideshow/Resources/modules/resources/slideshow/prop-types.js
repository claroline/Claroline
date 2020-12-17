import {PropTypes as T} from 'prop-types'

const Slide = {
  propTypes: {
    id: T.string.isRequired,
    content: T.shape({
      mimeType: T.string.isRequired,
      // TODO : enhance to allow more than files
      url: T.string
    }).isRequired,
    meta: T.shape({
      title: T.string,
      description: T.string
    }),
    display: T.shape({
      color: T.string
    })
  },
  defaultProps: {
    meta: {},
    display: {}
  }
}

const Slideshow = {
  propTypes: {
    id: T.string.isRequired,
    autoPlay: T.bool,
    interval: T.number,
    display: T.shape({
      showOverview: T.bool,
      showControls: T.bool
    }),
    slides: T.arrayOf(T.shape(
      Slide.propTypes
    ))
  },
  defaultProps: {
    autoPlay: false,
    interval: 5000,
    display: {
      showOverview: false,
      showControls: false
    }
  }
}

export {
  Slideshow,
  Slide
}
