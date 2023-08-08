import {PropTypes as T} from 'prop-types'

const Card = {
  propTypes: {
    id: T.string.isRequired,
    content: T.shape({
      mimeType: T.string,
    }),
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

const FlashcardDeck = {
  propTypes: {
    id: T.string,
    autoPlay: T.bool,
    interval: T.number,
    display: T.shape({
      showOverview: T.bool,
      showControls: T.bool
    }),
    Cards: T.arrayOf(T.shape(
      Card.propTypes
    ))
  }
}

export {
  FlashcardDeck,
  Card
}
