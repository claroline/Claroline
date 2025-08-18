import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ClozeText} from '#/plugin/exo/items/cloze/components/text'
import {ExpectedAnswerHole} from '#/plugin/exo/items/cloze/components/holes'

const ClozeExpectedAnswer = (props) =>
  <ClozeText
    anchorPrefix="cloze-hole-expected"
    className="cloze-paper"
    text={props.item.text}
    holes={props.item.holes.map(hole => {
      let solution = props.item.solutions.find(solution => solution.holeId === hole.id)

      return {
        id: hole.id,
        component: (
          <ExpectedAnswerHole
            showScore={props.showScore}
            id={hole.id}
            choices={hole.choices}
            size={hole.size}
            solutions={solution.answers}
          />
        )
      }
    })}
  />

ClozeExpectedAnswer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    text: T.string.isRequired,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired,
      choices: T.arrayOf(T.string)
    })).isRequired,
    solutions: T.arrayOf(T.object),
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  showScore: T.bool.isRequired
}

export {
  ClozeExpectedAnswer
}
