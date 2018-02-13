import React, {Component} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {asset} from '#/main/core/scaffolding/asset'
import {trans} from '#/main/core/translation'
import {makeCancelable} from '#/main/core/api/utils'
import {generateUrl} from '#/main/core/api/router'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

// todo : separate async call and presentational components

class LocaleGroup extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fetched: false,
      locales: {}
    }

    // retrieve locales
    this.fetchLocales()
  }

  fetchLocales() {
    this.pending = makeCancelable(
      fetch(
        generateUrl('apiv2_locale_list'),
        {credentials: 'include'}
      )
      .then(response => response.json())
      .then(
        (data) => {
          this.loadLocales(data)
          this.pending = null
        },
        () => this.pending = null
      )
    )
  }

  loadLocales(locales) {
    this.setState({
      fetched: true,
      locales: locales.filter(locale => !this.props.onlyEnabled || locale.enabled).map(locale => locale.name)
    })
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
    }
  }

  render() {
    return (
      <FormGroup
        {...this.props}
      >
        {!this.state.fetched &&
          <div>{trans('Please wait while we load locales...')}</div>
        }

        {this.state.fetched &&
          <div className="locales" role="checklist">
            {this.state.locales.map(locale =>
              <TooltipButton
                id={`btn-${locale}`}
                key={locale}
                title={trans(locale)}
                className={classes('locale-btn', {
                  active: locale === this.props.value
                })}
                onClick={() => this.props.onChange(locale)}
              >
                <svg className="locale-icon">
                  <use xlinkHref={`${asset('bundles/clarolinecore/images/locale-icons.svg')}#icon-locale-${locale}`} />
                </svg>
              </TooltipButton>
            )}
          </div>
        }
      </FormGroup>
    )
  }
}

implementPropTypes(LocaleGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  onlyEnabled: T.bool
}, {
  onlyEnabled: true
})

export {
  LocaleGroup
}
