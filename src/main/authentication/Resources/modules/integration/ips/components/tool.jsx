import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/authentication/integration/ips/store/selectors'
import {MODAL_IP_PARAMETERS} from '#/main/authentication/integration/ips/modals/parameters'

const IpsTool = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('ips', {}, 'integration'),
      target: `${props.path}/ips`
    }]}
    subtitle={trans('ips', {}, 'integration')}
    /*primaryAction="add-ip"*/
    primaryAction={
      {
        name: 'add-ip',
        type: MODAL_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_ip', {}, 'security'),
        primary: true,
        modal: [MODAL_IP_PARAMETERS, {
          onSave: () => props.invalidateList()
        }]
      }
    }
  >
    <Alert type="info" style={{marginTop: 20}}>
      {trans('ips_help', {}, 'security')}
    </Alert>

    <ListData
      name={selectors.LIST_NAME}
      fetch={{
        url: ['apiv2_ip_user_list'],
        autoload: true
      }}
      delete={{
        url: ['apiv2_ip_user_delete_bulk'],
        disabled: (rows) => -1 === rows.findIndex(row => !row.restrictions.locked)
      }}
      definition={[
        {
          name: 'ip',
          label: trans('ip_address'),
          type: 'string',
          displayed: true,
          calculated: (row) => {
            if (Array.isArray(row.ip)) {
              return `[ ${row.ip[0]}, ${row.ip[1]} ]`
            }

            return row.ip
          }
        }, {
          name: 'user',
          label: trans('user'),
          type: 'user',
          displayed: true
        }, {
          name: 'restrictions.locked',
          alias: 'locked',
          label: trans('locked'),
          type: 'boolean',
          displayed: true
        }
      ]}
      actions={(rows) => [
        {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_IP_PARAMETERS, {
            ip: rows[0],
            onSave: () => props.invalidateList()
          }],
          disabled: rows[0].restrictions.locked,
          scope: ['object'],
          group: trans('management')
        }
      ]}
    />
  </ToolPage>

IpsTool.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired
}

export {
  IpsTool
}
