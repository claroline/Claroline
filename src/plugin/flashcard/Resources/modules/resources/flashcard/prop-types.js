import {PropTypes as T} from 'prop-types'

const Card = {
  propTypes: {
    id: T.string.isRequired,
    question: T.string,
    visibleContent: T.string,
    hiddenContent: T.string
  },
  defaultProps: {
    children: []
  }
}

const FlashcardDeck = {
  propTypes: {
    id: T.string,
    display: T.shape({
      showOverview: T.bool
    }),
    cards: T.arrayOf(T.shape(
      Card.propTypes
    )),
    overview: T.shape({
      display: T.bool,
      message: T.string
    }),
    end: T.shape({
      display: T.bool,
      message: T.string,
      navigation: T.bool
    })
  },
  defaultProps: {
    display: {
      showOverview: false,
      showEndPage: false
    },
    cards: []
  }
}

export {
  Card,
  FlashcardDeck
}