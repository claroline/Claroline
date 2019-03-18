import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import merge from 'lodash/merge'

import {getLocale} from '#/main/app/intl/locale'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

const TranslatedInput = props =>
  <HtmlInput
    id={props.id}
    className={props.className}
    value={props.value[getLocale()]}
    minRows={props.minRows}
    disabled={props.disabled}
    onChange={(translatedValue) => {
      props.onChange(merge({}, props.value, {[getLocale()]: translatedValue }))
    }}
    onClick={props.onClick}
    onSelect={props.onSelect}
    onChangeMode={props.onChangeMode}
  />

implementPropTypes(TranslatedInput, FormFieldTypes, {
  // more precise value type
  value: T.object,

  // custom props
  minRows: T.number,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func
})

export {
  TranslatedInput
}
