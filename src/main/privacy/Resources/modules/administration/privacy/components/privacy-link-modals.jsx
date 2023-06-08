import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import { MODAL_BUTTON } from '#/main/app/buttons'
import { MODAL_COUNTRY_STORAGE } from '#/main/privacy/administration/privacy/modals/country'
import { MODAL_INFO_DPO } from '#/main/privacy/administration/privacy/modals/dpo'
import { MODAL_TERMS_OF_SERVICE } from '#/main/privacy/administration/privacy/modals/terms'

import { Button } from '#/main/app/action/components/button'
import { AlertBlock } from '#/main/app/alert/components/alert-block'

const PrivacyLinkModals = (props) => {
  const blocks = [
    {
      type: props.item.countryStorage ? 'success' : 'warning',
      titleKey: props.item.countryStorage ? 'countryStorage_ok' : 'no_countryStorage',
      labelKey: props.item.countryStorage ? 'change_country_storage' : 'add_country_storage',
      modalType: MODAL_COUNTRY_STORAGE,
      modalData: {
        countryStorage: props.item.countryStorage
      },
      helpKey: 'country_storage_help'
    },
    {
      type: props.item.dpo.email ? 'success' : 'warning',
      titleKey: props.item.dpo.email ? 'dpo_ok' : 'no_dpo',
      labelKey: props.item.dpo.email ? 'change_dpo' : 'add_dpo',
      modalType: MODAL_INFO_DPO,
      modalData: {
        dpo: props.item.dpo
      },
      helpKey: 'dpo_help'
    },
    {
      type: props.item.termsOfService ? 'success' : 'warning',
      titleKey: props.item.termsOfService ? 'terms_ok' : 'no_terms',
      labelKey: props.item.termsOfService ? 'change_terms' : 'add_terms',
      modalType: MODAL_TERMS_OF_SERVICE,
      modalData: {
        termsOfService: props.item.termsOfService,
        termsOfServiceEnabled: props.item.termsOfServiceEnabled
      },
      helpKey: 'terms_help'
    }
  ]

  return (
    <div className="modal-body">
      {blocks.map((block, index) => (
        <AlertBlock key={index} type={block.type} title={trans(block.titleKey, {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans(block.labelKey, {}, 'privacy')}
            modal={[block.modalType, block.modalData]}
          />
          <span className="help-block">
            {trans(block.helpKey, {}, 'privacy')}
          </span>
          {block.type === 'warning' && block.modalType === MODAL_TERMS_OF_SERVICE && (
            block.modalData.termsOfServiceEnabled ? (
              <p>{trans('terms_enabled', {}, 'privacy')}</p>
            ) : (
              <p style={{ color: 'red' }}>{trans('terms_not_enabled', {}, 'privacy')}</p>
            )
          )}
        </AlertBlock>
      ))}
    </div>
  )
}

PrivacyLinkModals.propTypes = {
  item: T.object.isRequired
}

export { PrivacyLinkModals }

