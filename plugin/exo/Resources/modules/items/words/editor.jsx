import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/core/translation'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {KeywordItems} from './../components/keywords.jsx'
import {actions} from './editor.js'

export const Words = props =>
  <fieldset className="words-editor">
    <CheckGroup
      checkId={`item-${props.item.id}-_wordsCaseSensitive`}
      label={tex('words_show_case_sensitive_option')}
      checked={props.item._wordsCaseSensitive}
      onChange={checked =>
        props.onChange(actions.updateProperty('_wordsCaseSensitive', checked))
      }
    />

    <KeywordItems
      showCaseSensitive={props.item._wordsCaseSensitive}
      showScore={true}
      keywords={props.item.solutions}
      validating={props.validating}
      _errors={props.item._errors && props.item._errors.keywords ? props.item._errors.keywords : {}}
      addKeyword={() => props.onChange(
        actions.addSolution()
      )}
      updateKeyword={(keyword, property, value) => props.onChange(
        actions.updateSolution(keyword, property, value)
      )}
      removeKeyword={(keyword) => props.onChange(
        actions.removeSolution(keyword)
      )}
    />
  </fieldset>

Words.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    _wordsCaseSensitive: T.bool.isRequired,
    _errors: T.shape({
      keywords: T.object
    })
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}
