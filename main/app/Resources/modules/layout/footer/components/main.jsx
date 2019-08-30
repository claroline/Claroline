import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {HtmlText} from '#/main/core/layout/components/html-text'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'
import {MODAL_LOCALE} from '#/main/app/modals/locale'

const FooterMain = (props) =>
  <footer className="app-footer-container">
    <div className="app-footer" role="presentation">
      {props.content &&
        <HtmlText className="app-footer-content">{props.content}</HtmlText>
      }

      <div className="app-footer-brand">
        <img src={asset('bundles/clarolinecore/images/logos/logo-sm.svg')} alt="logo" />
        <div role="presentation">
          <a href="http://www.claroline.net">Claroline Connect</a>
          <small>{param('version')}</small>
        </div>
      </div>

      {props.showLocale &&
        <Button
          className="app-current-locale btn-link"
          type={MODAL_BUTTON}
          modal={[MODAL_LOCALE, props.locale]}
          icon={<LocaleFlag locale={props.locale.current} />}
          label={trans(props.locale.current)}
        />
      }
    </div>
  </footer>

FooterMain.propTypes = {
  showLocale: T.bool.isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  content: T.string
}

export {
  FooterMain
}
