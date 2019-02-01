import React from 'react'
import {LINK_BUTTON} from '#/main/app/buttons'

import {BadgeForm} from  '#/plugin/open-badge/tools/badges/badge/components/badge-form'

const Badge = () =>
  <div>
    <BadgeForm
      name="badges.current"
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
