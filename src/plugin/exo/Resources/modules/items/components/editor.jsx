import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Badge} from '#/main/app/components/badge'
import {Toolbar} from '#/main/app/action'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {Html} from '#/main/app/components/html'

import {Item as ItemTypes} from '#/plugin/exo/items/prop-types'
import ScoreNone from '#/plugin/exo/scores/none'
import {Metadata as ItemMetadata} from '#/plugin/exo/items/components/metadata'
import {getComponent} from '#/plugin/exo/items/item-types'
import {calculateTotal} from '#/plugin/exo/items/score'
import {getItem} from '#/plugin/exo/items'
import {ItemIcon} from '#/plugin/exo/items/components/icon'

const ItemEditor = ({
  id,
  item,
  numbering,
  actions
}) => {
  const [itemDefinition, setItemDefinition] = useState(null)
  useEffect(() => {
    if (item.type) {
      getItem(item.type).then(setItemDefinition)
    }
  }, [item.type])

  let totalScore
  if (item.hasExpectedAnswers) {
    totalScore = calculateTotal(item)
  }

  const expectedAnswerComponent = getComponent(item.type, 'expectedAnswer')

  return (
    <div id={id} className="card quiz-item item-paper">
      <div className="card-header d-flex align-items-center gap-2">
        {itemDefinition &&
          <span className="mb-0 flex-fill fw-bolder">
            <ItemIcon name={get(itemDefinition, 'name')} className="icon-with-text-right" size="xs" />
            {trans(get(itemDefinition, 'name'), {}, 'question_types')}
          </span>
        }

        {(totalScore || 0 === totalScore) ?
          <Badge variant="primary" subtle={true} className="fs-base">
            {transChoice('solution_score', totalScore, {score: totalScore}, 'quiz')}
          </Badge> :
          <Badge variant="secondary" subtle={true} className="fs-base">
            {trans('score_none', {}, 'quiz')}
          </Badge>
        }

        {actions &&
          <Toolbar
            className="me-n2"
            buttonName="btn btn-text-body px-2"
            actions={actions}
            toolbar="edit more"
            tooltip="bottom"
          />
        }
      </div>

      <div className="card-body">
        <ItemMetadata
          item={item}
          showTitle={true}
          numbering={numbering}
        />

        {expectedAnswerComponent &&
          <hr className="item-content-separator my-4" />
        }

        {expectedAnswerComponent && createElement(expectedAnswerComponent, {
          item: item,
          showScore: item.hasExpectedAnswers && ScoreNone.name !== get(item, 'score.type')
        })}

        {(item.feedback && !isHtmlEmpty(item.feedback)) &&
          <div className="item-feedback">
            <span className="fa fa-comment" />
            <Html>{item.feedback}</Html>
          </div>
        }
      </div>
    </div>
  )
}

ItemEditor.propTypes = {
  id: T.string.isRequired,
  item: T.shape(
    ItemTypes.propTypes
  ).isRequired,
  numbering: T.string,
  actions: T.array
}

export {
  ItemEditor
}
