import {PropTypes as T} from 'prop-types'

const Card = {
  propTypes: {
    id: T.string,
    question: T.string,
    visibleContent: T.oneOfType([
      T.string,
      T.object
    ]),
    hiddenContent: T.oneOfType([
      T.string,
      T.object
    ]),
    visibleContentType: T.string,
    hiddenContentType: T.string
  },
  defaultProps: {}
}

const FlashcardDeck = {
  propTypes: {
    id: T.string,
    name: T.string,
    showProgression: T.bool,
    showLeitnerRules: T.bool,
    customButtons: T.bool,
    rightButtonLabel: T.string,
    wrongButtonLabel: T.string,
    draw: T.number,
    overview: T.shape({
      display: T.bool,
      message: T.string
    }),
    end: T.shape({
      display: T.bool,
      message: T.string,
      navigation: T.bool
    }),
    cards: T.arrayOf(T.shape(
      Card.propTypes
    ))
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
