import React, {PureComponent} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'

class LocaleButton extends PureComponent {
  constructor(props) {
    super(props)

    this.onClick = this.onClick.bind(this)
  }

  onClick() {
    this.props.onClick(this.props.locale)
  }

  render() {
    return (
      <Button
        type={CALLBACK_BUTTON}
        label={trans(this.props.locale)}
        tooltip="bottom"
        className={classes('btn-link locale-btn', {
          active: this.props.active
        })}
        callback={this.onClick}
      >
        <LocaleFlag locale={this.props.locale} />
      </Button>
    )
  }
}

LocaleButton.propTypes = {
  locale: T.string.isRequired,
  active: T.bool.isRequired,
  onClick: T.func.isRequired
}

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
    let available = this.props.available
    if (!available) {
      available = param('locale.available')
    }

    return (
      <div className={classes('locales', this.props.className)} role="checklist">
        {available.map(locale =>
          <LocaleButton
            key={locale}
            locale={locale}
            active={this.props.multiple && this.props.value ? -1 !== this.props.value.indexOf(locale) : locale === this.props.value}
            onClick={this.onChange}
          />
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
