import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {keywords as keywordUtils} from '#/plugin/exo/utils/keywords'
import cloneDeep from 'lodash/cloneDeep'

import {WordsItem as WordsItemTypes} from '#/plugin/exo/items/words/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {KeywordItems} from '#/plugin/exo/components/keywords'

const WordsEditor = (props) => {
  const KeyWordItems =
    <KeywordItems
      showCaseSensitive={props.item._wordsCaseSensitive}
      showScore={props.item.hasExpectedAnswers && props.hasAnswerScores}
      hasExpectedAnswers={props.item.hasExpectedAnswers}
      keywords={props.item.solutions}
      validating={props.validating}
      addKeyword={() => {
        const solutions = cloneDeep(props.item.solutions)
        solutions.push(keywordUtils.createNew())
        const deletable = solutions.length > 1
        solutions.forEach(solution => solution._deletable = deletable)
        props.update('solutions', solutions)
      }}
      updateKeyword={(keyword, property, value) => {
        const solutions = cloneDeep(props.item.solutions)
        value = property === 'score' ? parseFloat(value) : value
        const solution = solutions.find(solution => solution._id === keyword)
        // Retrieve the keyword to update
        if (solution) {
          solution[property] = value
        }

        props.update('solutions', solutions)
      }}
      removeKeyword={(keyword) => {
        const solutions = cloneDeep(props.item.solutions)

        const solution = solutions.find(solution => solution._id === keyword)
        if (solution) {
          solutions.splice(solutions.indexOf(solution), 1)
          solutions.forEach(solution => solution._deletable = props.item.solutions.length > 1)
        }

        props.update('solutions', solutions)
      }}
    />

  return (
    <FormData
      className="words-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
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
              name: '_wordsCaseSensitive',
              type: 'boolean',
              label: trans('words_show_case_sensitive_option', {}, 'quiz'),
              required: true
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
