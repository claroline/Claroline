import React from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {ClozeText} from '#/plugin/exo/items/cloze/components/text'
import {PlayerHole} from '#/plugin/exo/items/cloze/components/holes'

export const ClozePlayer = props =>
  <ClozeText
    anchorPrefix="cloze-hole-player"
    className="cloze-player"
    text={props.item.text}
    holes={props.item.holes.map(hole => {
      let answer = props.answer.find(holeAnswer => holeAnswer.holeId === hole.id)

      return {
        id: hole.id,
        component: (
          <PlayerHole
            id={hole.id}
            size={hole.size}
            answer={answer ? answer.answerText : ''}
            random={hole.random}
            choices={hole.choices}
            disabled={props.disabled}
            onChange={(newAnswer) => {
              const answers = cloneDeep(props.answer)

              let holeAnswer = answers.find(item => item.holeId === hole.id)
              if (holeAnswer) {
                holeAnswer.answerText = newAnswer
              } else {
                answers.push({
                  holeId: hole.id,
                  answerText: newAnswer
                })
              }

              props.onChange(answers)
            }}
          />
        )
      }
    })}
  />

ClozePlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    holes: T.array.isRequired,
    solutions: T.array.isRequired,
    text: T.string.isRequired
  }).isRequired,
  answer: T.arrayOf(T.shape({
    holeId: T.string.isRequired,
    answerText: T.string.isRequired
  })),
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

ClozePlayer.defaultProps = {
  answer: [],
  disabled: false
}
