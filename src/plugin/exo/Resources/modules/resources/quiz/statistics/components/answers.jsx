import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentTitle} from '#/main/app/content/components/title'

import {isQuestionType} from '#/plugin/exo/items/item-types'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {ItemStats} from '#/plugin/exo/items/components/stats'
import {PageSection} from '#/main/app/page'

const AnswersStats = props =>
  <PageSection className="quiz-statistics" size="lg">
    {props.quiz.steps
      .filter(step => step.items && 0 < step.items.length)
      .map((step, idx) => {
        return (
          <div key={idx} className="">
            {props.showTitles &&
              <ContentTitle
                level={3}
                displayLevel={2}
                numbering={getNumbering(props.numbering, idx)}
                title={step.title || trans('step', {number: idx + 1}, 'quiz')}
              />
            }

            {step.items
              .filter(item => isQuestionType(item.type) && props.stats && props.stats[item.id])
              .map((item, idxItem) => (
                <ItemStats
                  key={item.id}
                  item={item}
                  showTitle={props.showQuestionTitles}
                  numbering={getNumbering(props.questionNumbering, idx, idxItem)}
                  stats={props.stats[item.id]}
                />
              ))
            }
          </div>
        )
      })
    }
  </PageSection>

AnswersStats.propTypes = {
  numbering: T.string,
  questionNumbering: T.string,
  showTitles: T.bool,
  showQuestionTitles: T.bool,
  quiz: T.object.isRequired,
  stats: T.object
}

export {
  AnswersStats
}
