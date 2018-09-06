import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {param} from '#/main/app/config'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button'

const Locale = props =>
  <div className="locales" role="checklist">
    {param('locale.available').map(locale =>
      <TooltipButton
        id={`btn-${locale}`}
        key={locale}
        title={trans(locale)}
        className={classes('locale-btn', {
          active: locale === props.value
        })}
        onClick={() => props.onChange(locale)}
      >
        <LocaleFlag locale={props.value} />
      </TooltipButton>
    )}
  </div>

implementPropTypes(Locale, FormFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  Locale
}
