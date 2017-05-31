import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ClozeText} from './utils/cloze-text.jsx'
import {UserAnswerHole} from './utils/cloze-holes.jsx'

export const ClozeFeedback = props =>
  <ClozeText
    anchorPrefix="cloze-hole-feedback"
    className="cloze-feedback"
    text={props.item.text}
    holes={props.item.holes.map(hole => {
      let answer = props.answer.find(holeAnswer => holeAnswer.holeId === hole.id)
      let solution = props.item.solutions.find(solution => solution.holeId === hole.id)

      return {
        id: hole.id,
        component: (
          <UserAnswerHole
            id={hole.id}
            answer={answer ? answer.answerText : null}
            choices={hole.choices}
            showScore={false}
            solutions={solution.answers}
          />
        )
      }
    })}
  />

ClozeFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    text: T.string.isRequired,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired,
      choices: T.arrayOf(T.string)
    })).isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.array.isRequired
}

ClozeFeedback.defaultProps = {
  answer: []
}
