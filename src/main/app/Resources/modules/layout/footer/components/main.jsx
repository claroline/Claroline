import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'
import {MODAL_LOCALE} from '#/main/app/modals/locale'
import {MODAL_TERMS_OF_SERVICE} from '#/main/app/modals/terms-of-service'

const FooterMain = (props) =>
  <footer className="app-footer-container">
    <div className="app-footer" role="presentation">
      {props.content &&
        <ContentHtml className="app-footer-content">{props.content}</ContentHtml>
      }

      <a className="app-footer-brand" href="https://www.claroline.com">
        <img src={asset('bundles/clarolinecore/images/logos/logo-sm.svg')} alt="logo" />

        <span className="hidden-xs">Claroline Connect</span>

        <small>{props.version}</small>
      </a>

      {props.display.termsOfService &&
        <Button
          className="app-footer-btn btn-link"
          type={MODAL_BUTTON}
          icon="fa fa-fw fa-copyright visible-xs"
          label={<span key="label" className="hidden-xs">{trans('terms_of_service')}</span>}
          modal={[MODAL_TERMS_OF_SERVICE]}
        />
      }

      {props.display.help && props.helpUrl &&
        <Button
          className="app-footer-btn btn-link"
          type={URL_BUTTON}
          icon="fa fa-fw fa-question-circle-o visible-xs"
          label={<span key="label" className="hidden-xs">{trans('help')}</span>}
          target={props.helpUrl}
        />
      }

      {props.display.locale &&
        <Button
          className="app-current-locale app-footer-btn btn-link"
          type={MODAL_BUTTON}
          modal={[MODAL_LOCALE, props.locale]}
          icon={<LocaleFlag className="action-icon" locale={props.locale.current} />}
          label={<span key="label" className="hidden-xs">{trans(props.locale.current)}</span>}
        />
      }
    </div>
  </footer>

FooterMain.propTypes = {
  version: T.string.isRequired,
  display: T.shape({
    locale: T.bool.isRequired,
    help: T.bool.isRequired,
    termsOfService: T.bool.isRequired
  }).isRequired,
  helpUrl: T.string,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  content: T.string
}

export {
  FooterMain
}
