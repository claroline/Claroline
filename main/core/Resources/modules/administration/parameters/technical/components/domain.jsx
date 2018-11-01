import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const DomainComponent = () =>
  <FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('internet'),
        defaultOpened: true,
        fields: [
          {
            name: 'internet.domain_name',
            type: 'string',
            label: trans('domain_name'),
            required: false
          }/*,
          {
            name: 'internet.platform_url',
            type: 'string',
            label: trans('platform_url'),
            required: false
          }*/,
          {
            name: 'internet.google_meta_tag',
            type: 'string',
            label: trans('google_tag_validation'),
            required: false
          }
        ]
      },
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('ssl'),
        defaultOpened: false,
        fields: [
          {
            name: 'ssl.enabled',
            type: 'boolean',
            label: trans('ssl_enabled'),
            required: false
          },
          {
            name: 'ssl.version',
            type: 'string',
            label: trans('version'),
            required: false
          }
        ]
      }
    ]}
  />


DomainComponent.propTypes = {
}

const Domain = connect(
  null,
  () => ({ })
)(DomainComponent)

export {
  Domain
}
