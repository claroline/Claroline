import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/plugin/open-badge/tools/badges/store'
import {BadgeForm} from '#/plugin/open-badge/badge/components/form'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'

const BadgeCreate = () =>
  <ToolPage
    className="badge-page"
    icon={
      <BadgeImage size="xl" />
    }
    title={trans('new_badge', {}, 'badge')}
  >
    <BadgeForm name={selectors.FORM_NAME} />
  </ToolPage>

export {
  BadgeCreate
}
