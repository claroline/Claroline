import React, {PureComponent} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {Radio} from '#/main/app/input/components/radio'
import {CountryFlag} from '#/main/app/components/country-flag'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

class LocaleInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(locale) {
    let newValue
    if (!this.props.multiple) {
      newValue = locale
    } else {
      newValue = this.props.value ? [].concat(this.props.value) : []

      const localePos = newValue.indexOf(locale)
      if (-1 !== localePos) {
        // remove locale from list
        newValue.splice(localePos, 1)
      } else {
        // add locale to list
        newValue.push(locale)
      }
    }

    this.props.onChange(newValue)
  }

  render() {
    const available = param('locale.available')

    return (
      <div className={classes('locales d-flex flex-column gap-1', this.props.className)} role="presentation">
        {available.map(locale =>
          <div key={locale} className={classes('px-3 py-2 rounded-2', {
            'bg-body-secondary': locale === this.props.value,
            'bg-body-tertiary': locale !== this.props.value,
          })} role="presentation">
            <Radio
              className="mb-0"
              id={locale}
              label={
                <div className="d-flex flex-row justify-content-between gap-2" role="presentation">
                  {trans(locale)}
                  <CountryFlag countryCode={'en' === locale ? 'gb' : locale} />
                </div>
              }
              value={locale}
              checked={locale === this.props.value}
              onChange={this.onChange}
            />
          </div>
        )}
      </div>
    )
  }
}

implementPropTypes(LocaleInput, DataInputTypes, {
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
  LocaleInput
}
