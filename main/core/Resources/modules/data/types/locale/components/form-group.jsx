import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'
import {t} from '#/main/core/translation'
import {makeCancelable} from '#/main/core/api/utils'
import {generateUrl} from '#/main/core/fos-js-router'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

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
    let localeUrl = generateUrl('apiv2_locale_list')

    this.pending = makeCancelable(
      fetch(localeUrl, {credentials: 'include'})
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
          <div>{t('Please wait while we load locales...')}</div>
        }

        {this.state.fetched &&
          <div role="checklist">
            {this.state.locales.map(locale =>
              <TooltipButton
                id={`btn-${locale}`}
                key={locale}
                title={t(locale)}
                className="locale-btn"
                onClick={() => true}
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

LocaleGroup.propTypes = {
  onlyEnabled: T.bool
}

LocaleGroup.defaultProps = {
  onlyEnabled: true
}

export {
  LocaleGroup
}
