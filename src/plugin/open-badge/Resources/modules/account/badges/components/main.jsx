import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {AccountPage} from '#/main/app/account/containers/page'
import {route} from '#/main/app/account/routing'
import {route as toolRoute} from '#/main/core/tool/routing'

import {selectors} from '#/plugin/open-badge/account/badges/store/selectors'
import {AssertionBadgeCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'

const BadgesMain = () =>
  <AccountPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('my_badges', {}, 'badge'),
        target: route('badges')
      }
    ]}
    title={trans('my_badges', {}, 'badge')}
  >
    <div style={{
      marginTop: 60 // TODO : manage spacing correctly
    }}>
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_assertion_current_user_list'],
          autoload: true
        }}
        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: `${toolRoute('badges')}/badges/${row.badge.id}/assertion/${row.id}`,
          label: trans('open', {}, 'actions')
        })}
        definition={[
          {
            name: 'badge.name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'issuedOn',
            label: trans('granted_date', {}, 'badge'),
            type: 'date',
            displayed: true,
            primary: true,
            options: {
              time: true
            }
          }, {
            name: 'badge.meta.enabled',
            type: 'boolean',
            label: trans('enabled'),
            displayed: true
          }
        ]}
        card={AssertionBadgeCard}
        display={{current: listConstants.DISPLAY_LIST_SM}}
      />
    </div>
  </AccountPage>

export {
  BadgesMain
}
