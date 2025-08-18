import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {keywords as keywordUtils} from '#/plugin/exo/utils/keywords'

import {WordsItem as WordsItemTypes} from '#/plugin/exo/items/words/prop-types'
import {FormContent} from '#/main/app/content/form/containers/content'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {KeywordItems} from '#/plugin/exo/components/keywords'
import {makeId} from '#/main/app/utils/id'

const WordsEditor = (props) => {
  let newSolutions = []
  if (props.item.solutions) {
    newSolutions = props.item.solutions.map(solution => Object.assign({}, solution, {
      _id: solution._id ? solution._id : makeId(),
      _deletable: 1 < props.item.solutions.length
    }))
  }

  const KeyWordItems =
    <KeywordItems
      showCaseSensitive={props.item._wordsCaseSensitive}
      showScore={props.item.hasExpectedAnswers && props.hasAnswerScores}
      hasExpectedAnswers={props.item.hasExpectedAnswers}
      keywords={newSolutions}
      contentType={props.item.contentType}
      addKeyword={() => {
        newSolutions.push(keywordUtils.createNew())
        newSolutions.forEach(solution => solution._deletable = newSolutions.length > 1)

        props.update('solutions', newSolutions)
      }}
      updateKeyword={(keyword, property, value) => {
        value = property === 'score' ? parseFloat(value) : value
        const solution = newSolutions.find(solution => solution._id === keyword)
        // Retrieve the keyword to update
        if (solution) {
          solution[property] = value
        }

        props.update('solutions', newSolutions)
      }}
      removeKeyword={(keyword) => {
        const solutionPos = newSolutions.findIndex(solution => solution._id === keyword)
        if (-1 !== solutionPos) {
          newSolutions.splice(solutionPos, 1)
          newSolutions.forEach(solution => solution._deletable = newSolutions.length > 1)
        }

        props.update('solutions', newSolutions)
      }}
    />

  return (
    <FormContent
      className="words-editor"
      name={props.formName}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'solutions',
              label: trans('keywords'),
              required: true,
              component: KeyWordItems
            }, {
              name: 'contentType',
              type: 'choice',
              label: trans('type'),
              required: true,
              options: {
                condensed: true,
                choices: {
                  text: trans('text'),
                  date: trans('date')
                }
              },
              linked: [
                {
                  name: '_wordsCaseSensitive',
                  type: 'boolean',
                  label: trans('words_show_case_sensitive_option', {}, 'quiz'),
                  displayed: 'text' === props.item.contentType,
                  required: true
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(WordsEditor, ItemEditorTypes, {
  item: T.shape(WordsItemTypes.propTypes).isRequired
})

export {
  WordsEditor
}
