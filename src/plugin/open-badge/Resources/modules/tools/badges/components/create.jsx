import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/open-badge/tools/badges/store'
import {BadgeForm} from '#/plugin/open-badge/badge/components/form'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'

const BadgeCreate = () => {
  const path = useSelector(toolSelectors.path)

  return (
    <ToolPage
      className="badge-page"
      path={[
        {
          type: LINK_BUTTON,
          label: trans('new_badge', {}, 'badge'),
          target: '' // current page, no need to add a link
        }
      ]}
      icon={
        <BadgeImage className="img-thumbnail" />
      }
      primaryAction="add"
      subtitle={trans('new_badge', {}, 'badge')}
      actions={[{
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_badge', {}, 'actions'),
        target: `${path}/new`,
        primary: true
      }]}
    >
      <BadgeForm name={selectors.FORM_NAME} />
    </ToolPage>
  )
}

export {
  BadgeCreate
}
