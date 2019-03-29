import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {keywords as keywordUtils} from '#/plugin/exo/utils/keywords'
import cloneDeep from 'lodash/cloneDeep'

import {WordsItem as WordsItemTypes} from '#/plugin/exo/items/words/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {KeywordItems} from '#/plugin/exo/components/keywords'

const WordsEditor = (props) =>
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
            name: '_wordsCaseSensitive',
            type: 'boolean',
            label: trans('words_show_case_sensitive_option', {}, 'quiz'),
            required: true
          }, {
            name: 'solutions',
            render: (item, wordErrors) => {
              const KeyWordItems = 
                <KeywordItems
                  showCaseSensitive={item._wordsCaseSensitive}
                  showScore={true}
                  keywords={item.solutions}
                  validating={props.validating}
                  _errors={wordErrors && wordErrors.keywords ? wordErrors.keywords : {}}
                  addKeyword={() => {
                    const solutions = cloneDeep(item.solutions)
                    solutions.push(keywordUtils.createNew())
                    const deletable = solutions.length > 1
                    solutions.forEach(solution => solution._deletable = deletable)
                    props.update('solutions', solutions)
                  }}
                  updateKeyword={(keyword, property, value) => {
                    const solutions = cloneDeep(item.solutions)
                    value = property === 'score' ? parseFloat(value) : value
                    const solution = solutions.find(solution => solution._id === keyword)
                    // Retrieve the keyword to update
                    if (solution) {
                      solution[property] = value
                    }

                    props.update('solutions', solutions)
                  }}
                  removeKeyword={(keyword) => {
                    const solutions = cloneDeep(item.solutions)

                    const solution = solutions.find(solution => solution._id === keyword)
                    if (solution) {
                      solutions.splice(solutions.indexOf(solution), 1)
                      solutions.forEach(solution => solution._deletable = item.solutions.length > 1)
                    }

                    props.update('solutions', solutions)
                  }}
                />
                
              return KeyWordItems
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(WordsEditor, ItemEditorTypes, {
  item: T.shape(WordsItemTypes.propTypes).isRequired
})

export {
  WordsEditor
}
