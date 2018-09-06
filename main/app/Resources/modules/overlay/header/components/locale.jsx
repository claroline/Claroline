import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'

// TODO : add tooltip on button
// TODO : add flags in dropdown

const HeaderLocale = props =>
  <MenuButton
    id="app-locale-select"
    className="app-header-locale app-header-item app-header-btn"
    menu={{
      position: 'bottom',
      align: 'right',
      label: trans('available_languages'),
      items: props.locale.available.map(locale => ({
        type: URL_BUTTON,
        label: trans(locale),
        target: ['claroline_locale_change', {locale: locale}],
        active: locale === props.locale.current
      }))
    }}
  >
    <LocaleFlag locale={props.locale.current} />
  </MenuButton>

HeaderLocale.propTypes = {
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired
}

export {
  HeaderLocale
}
