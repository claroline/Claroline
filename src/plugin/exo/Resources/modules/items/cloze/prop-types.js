import {PropTypes as T} from 'prop-types'

const ClozeItem = {
  propsTypes: {
    id: T.string.isRequired,
    text: T.string.isRequired,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired,
      size: T.number,
      placeholder: T.string,
      random: T.bool,
      choices: T.arrayOf(T.shape(T.string))
    })).isRequired,
    solutions: T.arrayOf(T.shape({
      holeId: T.string.isRequired,
      answers: T.arrayOf(T.shape({
        text: T.string.isRequired,
        caseSensitive: T.bool,
        score: T.number,
        feedback: T.string
      }))
    })).isRequired
  },
  defaultProps: {
    solutions: [],
    holes: []
  }
}

export {
  ClozeItem
}