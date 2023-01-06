import React from 'react'
import {PropTypes as T} from 'prop-types'
import Panel from 'react-bootstrap/lib/Panel'

import {trans} from '#/main/app/intl/translation'
import {ContentTitle} from '#/main/app/content/components/title'

import {getDefinition, isQuestionType} from '#/plugin/exo/items/item-types'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'

const AnswersStats = props =>
  <div className="quiz-statistics">
    {props.quiz.steps
      .filter(step => step.items && 0 < step.items.length)
      .map((step, idx) => {
        const numbering = getNumbering(props.numbering, idx)

        return (
          <div key={idx} className="quiz-item item-paper">
            {props.showTitles &&
              <ContentTitle
                level={3}
                displayLevel={2}
                numbering={numbering}
                title={step.title || trans('step', {number: idx + 1}, 'quiz')}
              />
            }

            {step.items.map((item, idxItem) => {
              return isQuestionType(item.type) && props.stats && props.stats[item.id] &&
                <Panel key={item.id}>
                  {item.title &&
                    <h4 className="item-title">{item.title}</h4>
                  }

                  <ItemMetadata
                    item={item}
                    numbering={props.numbering !== constants.NUMBERING_NONE ? (idx + 1) + '.' + getNumbering(props.numbering, idxItem): null}
                  />

                  {React.createElement(getDefinition(item.type).paper, {
                    item,
                    showYours: false,
                    showExpected: false,
                    showStats: true,
                    showScore: false,
                    stats: props.stats && props.stats[item.id] ? props.stats[item.id] : {}
                  })}
                </Panel>
            })}
          </div>
        )
      })
    }
  </div>

AnswersStats.propTypes = {
  numbering: T.string,
  showTitles: T.bool,
  quiz: T.object.isRequired,
  stats: T.object
}

export {
  AnswersStats
}
