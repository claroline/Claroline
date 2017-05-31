import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PaperTabs} from '../components/paper-tabs.jsx'
import {ClozeText} from './utils/cloze-text.jsx'
import {UserAnswerHole, ExpectedAnswerHole} from './utils/cloze-holes.jsx'

export const ClozePaper = (props) => {
  return (
    <PaperTabs
      item={props.item}
      answer={props.answer}
      id={props.item.id}
      yours={
        <ClozeText
          anchorPrefix="cloze-hole-user"
          className="cloze-paper"
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
                  showScore={props.showScore}
                  solutions={solution.answers}
                />
              )
            }
          })}
        />
      }
      expected={
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
                  solutions={solution.answers}
                />
              )
            }
          })}
        />
      }
    />
  )
}

ClozePaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    text: T.string.isRequired,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired,
      choices: T.arrayOf(T.string)
    })).isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.array.isRequired,
  showScore: T.bool.isRequired
}

ClozePaper.defaultProps = {
  answer: []
}
