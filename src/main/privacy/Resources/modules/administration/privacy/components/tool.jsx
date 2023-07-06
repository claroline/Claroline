import React from 'react'
import { ToolPage } from '#/main/core/tool/containers/page'
import {trans} from '#/main/app/intl'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFO_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'

const PrivacyTool = (props) => {
  const blocks = [
    {
      type: props.countryStorage ? 'success' : 'warning',
      titleKey: props.countryStorage ? 'countryStorage_ok' : 'no_countryStorage',
      labelKey: props.countryStorage ? 'change_country_storage' : 'add_country_storage',
      modalType: MODAL_COUNTRY_STORAGE,
      modalData: {
        countryStorage: props.countryStorage,
        onSave: (data) => props.updateCountry(data)
      },
      helpKey: 'country_storage_help'
    },
    {
      type: props.dpo.email ? 'success' : 'warning',
      titleKey: props.dpo.email ? 'dpo_ok' : 'no_dpo',
      labelKey: props.dpo.email ? 'change_dpo' : 'add_dpo',
      modalType: MODAL_INFO_DPO,
      modalData: {
        dpo: props.dpo,
        onSave: (data) => props.updateDpo(data)
      },
      helpKey: 'dpo_help'
    },
    {
      type: props.termsOfService ? 'success' : 'warning',
      titleKey: props.termsOfService ? 'terms_ok' : 'no_terms',
      labelKey: props.termsOfService ? 'change_terms' : 'add_terms',
      modalType: MODAL_TERMS_OF_SERVICE,
      modalData: {
        termsOfService: props.termsOfService,
        termsOfServiceEnabled: props.termsOfServiceEnabled,
        onSave: (data) => {
          props.updateTermsOfService(data)
          props.updateTermsEnabled(data)
        }
      },
      helpKey: 'terms_help'
    }
  ]

  const renderValueOrUndefined = (value) => {
    return value !== '' ? value : <i>{trans('undefined', {}, 'privacy')}</i>
  }

  return(
    <ToolPage>
      <div className="panel panel-default" style={{marginTop: '25px'}}>
        <div className="panel-heading">
          {trans('country_storage', {}, 'privacy')}
        </div>
        <div className="panel-body">
          {renderValueOrUndefined(props.countryStorage)}
        </div>
      </div>
      <div className="panel panel-default">
        <div className="panel-heading">
          {trans('dpo_info', {}, 'privacy')}
        </div>
        <div className="panel-body">
          <strong>{trans('name')} : </strong>
          {renderValueOrUndefined(props.dpo.name)}
          <br/>
          <strong>{trans('email')} :</strong> {renderValueOrUndefined(props.dpo.email)}
          <br/>
          <strong>{trans('phone')} :</strong> {renderValueOrUndefined(props.dpo.phone)}
          <br/>
          <strong>{trans('address')} :</strong>
          <br/>
          {renderValueOrUndefined(props.dpo.address.street1)}<br/>
          {trans('complement_address', {}, 'privacy')} : {renderValueOrUndefined(props.dpo.address.street2)}<br/>
          {trans('postal_code')} : {renderValueOrUndefined(props.dpo.address.postalCode)}<br/>
          {trans('city', {}, 'privacy')} : {renderValueOrUndefined(props.dpo.address.city)}<br/>
          {trans('state', {}, 'privacy')} : {renderValueOrUndefined(props.dpo.address.state)}<br/>
          {trans('country')} : {renderValueOrUndefined(props.dpo.address.country)}
        </div>
      </div>

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
        </AlertBlock>
      ))}
    </ToolPage>
  )
}

export {
  PrivacyTool
}
