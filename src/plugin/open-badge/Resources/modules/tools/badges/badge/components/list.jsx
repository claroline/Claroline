import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {PageListSection} from '#/main/app/page'
import {actions as listActions} from '#/main/app/content/list'
import {selectors as toolSelectors, ToolPage} from '#/main/core/tool'

import {BadgeList as BaseBadgeList}  from '#/plugin/open-badge/badge/components/list'
import {selectors} from '#/plugin/open-badge/tools/badges/store'
import {MODAL_BADGE_CREATION} from '#/plugin/open-badge/badge/modals/creation'

const BadgeList = () => {
  const dispatch = useDispatch()

  const path = useSelector(toolSelectors.path)
  const poster = useSelector(toolSelectors.poster)
  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)
  const canEdit = useSelector((state) => toolSelectors.hasPermission('edit', state))

  return (
    <ToolPage
      title={trans('all_badges', {}, 'badge')}
    >
      <PageListSection
        poster={poster}
        title={trans('all_badges', {}, 'badge')}
        addAction={{
          name: 'add',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_badge', {}, 'actions'),
          displayed: canEdit,
          primary: true,
          modal: [MODAL_BADGE_CREATION, {
            onCreate: () => {
              dispatch(listActions.invalidateData(selectors.LIST_NAME))
            }
          }]
        }}
      >
        <BaseBadgeList
          className="mb-5"
          flush={true}
          path={path}
          name={selectors.LIST_NAME}
          url={'workspace' === contextType ?
            ['apiv2_badge_workspace_list', {workspace: contextId}] :
            ['apiv2_badge_list']
          }
          customDefinition={'workspace' !== contextType ? [
            {
              name: 'workspace',
              label: trans('workspace'),
              type: 'workspace',
              displayed: true,
              filterable: true
            }
          ] : []}
        />
      </PageListSection>
    </ToolPage>
  )
}

BadgeList.propTypes = {
  path: T.string.isRequired,
  poster: T.string,
  canEdit: T.bool.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string
}

export {
  BadgeList
}
