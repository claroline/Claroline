import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {asset} from '#/main/core/scaffolding/asset'
import {platformConfig} from '#/main/core/platform'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'

const Locale = props =>
  <div className="locales" role="checklist">
    {platformConfig('locale.available').map(locale =>
      <TooltipButton
        id={`btn-${locale}`}
        key={locale}
        title={trans(locale)}
        className={classes('locale-btn', {
          active: locale === props.value
        })}
        onClick={() => props.onChange(locale)}
      >
        <svg className="locale-icon">
          <use xlinkHref={`${asset('bundles/clarolinecore/images/locale-icons.svg')}#icon-locale-${locale}`} />
        </svg>
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
