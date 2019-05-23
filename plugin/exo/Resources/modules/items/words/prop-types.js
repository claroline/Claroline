import {PropTypes as T} from 'prop-types'


const WordsItem = {
  propTypes: {
    id: T.string.isRequired,
    solutions: T.arrayOf(T.shape({
      text: T.string.isRequired,
      caseSensitive: T.bool,
      score: T.number,
      feedback: T.string
    })).isRequired,
    _wordsCaseSensitive: T.bool.isRequired,
    _errors: T.shape({
      keywords: T.object
    })
  },
  defaultProps: {
    solutions: []
  }
}

export {
  WordsItem
}
