import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {showBreadcrumb} from '#/main/app/layout/utils'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {route as toolRoute} from '#/main/core/tool/routing'

import {selectors} from '#/plugin/open-badge/account/badges/store/selectors'
import {AssertionBadgeCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'

const BadgesMain = (props) =>
  <UserPage
    showBreadcrumb={showBreadcrumb()}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('my_account'),
        target: '/account'
      }, {
        type: LINK_BUTTON,
        label: trans('my_badges', {}, 'badge'),
        target: '/account/badges'
      }
    ]}
    title={trans('my_badges', {}, 'badge')}
    user={props.currentUser}
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
  </UserPage>

BadgesMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  BadgesMain
}
