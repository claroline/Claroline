import React from 'react'
import { ToolPage } from '#/main/core/tool/containers/page'
import { PrivacyLinkModals } from '#/main/privacy/administration/privacy/components/privacy-link-modals'
import {trans} from '#/main/app/intl'

const PrivacyTool = (props) =>
  <ToolPage>
    <div className="panel panel-default" style={{marginTop: '25px'}}>
      <div className="panel-heading">
        {trans('country_storage', {}, 'privacy')}
      </div>
      <div className="panel-body">
        {props.parameters.countryStorage}
      </div>
    </div>
    <div className="panel panel-default">
      <div className="panel-heading">
        {trans('dpo_info', {}, 'privacy')}
      </div>
      <div className="panel-body">
        <strong>Nom : </strong>
        {props.parameters.dpo.name}
        <br/>
        <strong>Email :</strong> {props.parameters.dpo.email}
        <br/>
        <strong>Téléphone :</strong> {props.parameters.dpo.phone}
        <br/>
        <strong>Adresse :</strong>
        <br/>
        {props.parameters.dpo.address.street1}<br/>
        {props.parameters.dpo.address.street2}<br/>
        {props.parameters.dpo.address.postalCode}<br/>
        {props.parameters.dpo.address.city}<br/>
        {props.parameters.dpo.address.state}<br/>
        {props.parameters.dpo.address.country}
      </div>
    </div>
    <PrivacyLinkModals item={props.parameters} />
  </ToolPage>

export {
  PrivacyTool
}
