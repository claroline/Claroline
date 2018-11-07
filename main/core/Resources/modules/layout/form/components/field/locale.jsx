import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button'

const Locale = props => {
  let available = props.available
  if (!available) {
    available = param('locale.available')
  }

  return (
    <div className="locales" role="checklist">
      {available.map(locale =>
        <TooltipButton
          id={`btn-${locale}`}
          key={locale}
          title={trans(locale)}
          className={classes('locale-btn', {
            active: props.multiple && props.value ? -1 !== props.value.indexOf(locale) : locale === props.value
          })}
          onClick={() => {
            let newValue
            if (!props.multiple) {
              newValue = locale
            } else {
              newValue = props.value ? [].concat(props.value) : []

              const localePos = newValue.indexOf(locale)
              if (-1 !== localePos) {
                // remove locale from list
                newValue.splice(localePos, 1)
              } else {
                // add locale to list
                newValue.push(locale)
              }
            }

            props.onChange(newValue)
          }}
        >
          <LocaleFlag locale={locale} />
        </TooltipButton>
      )}
    </div>
  )
}

implementPropTypes(Locale, FormFieldTypes, {
  // more precise value type
  value: T.oneOfType([
    T.string, // single locale
    T.arrayOf(T.string) // multiple locales
  ]),
  available: T.arrayOf(T.string),
  multiple: T.bool
}, {
  value: '',
  multiple: false
})

export {
  Locale
}
