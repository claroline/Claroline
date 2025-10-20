import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/components/alert'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'

import {selectors} from '#/main/authentication/administration/authentication/store'
import {MODAL_IP_FORM} from '#/main/authentication/ip/modals/form'

import {IpList} from '#/main/authentication/ip/components/list'

const AuthenticationIps = props =>
  <ToolPage
    title={trans('ips', {}, 'security')}
  >
    <PageListSection
      title={trans('ips', {}, 'security')}
      addAction={{
        name: 'add-ip',
        type: MODAL_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_ip', {}, 'security'),
        primary: true,
        modal: [MODAL_IP_FORM, {
          onSave: props.invalidateList
        }]
      }}
    >
      <Alert type="info" className="mb-0">
        {trans('ips_help', {}, 'security')}
      </Alert>

      <IpList
        className="mb-5"
        flush={true}
        name={selectors.STORE_NAME+'.ips'}
        url={['apiv2_ip_user_list']}
        definition={[
          {
            name: 'user',
            label: trans('user'),
            type: 'user',
            displayed: true
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

AuthenticationIps.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired
}

export {
  AuthenticationIps
}
