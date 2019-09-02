import React from 'react'
import {LINK_BUTTON} from '#/main/app/buttons'

import {BadgeForm} from  '#/plugin/open-badge/tools/badges/badge/components/form'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

const Badge = () =>
  <div>
    <BadgeForm
      name={selectors.STORE_NAME +'.badges.current'}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: '/badges',
        exact: true
      }}
    >
    </BadgeForm>
  </div>

export {
  Badge
}
