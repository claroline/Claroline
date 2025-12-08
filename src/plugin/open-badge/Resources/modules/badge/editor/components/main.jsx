import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useLocation} from 'react-router-dom'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {matchPath} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor'
import {selectors as toolSelectors} from '#/main/core/tool'

import {route} from '#/plugin/open-badge/badge/routing'
import {actions as baseActions} from '#/plugin/open-badge/tools/badges/store'
import {BadgeEditorOverview} from '#/plugin/open-badge/badge/editor/components/overview'
import {BadgeEditorPermissions} from '#/plugin/open-badge/badge/editor/components/permissions'
import {BadgeEditorAppearance} from '#/plugin/open-badge/badge/editor/components/appearance'
import {BadgeEditorAttribution} from '#/plugin/open-badge/badge/editor/components/attribution'
import {BadgeEditorActions} from '#/plugin/open-badge/badge/editor/components/actions'
import {actions, selectors} from '#/plugin/open-badge/badge/editor/store'

const BadgeEditor = () => {
  const dispatch = useDispatch()
  const location = useLocation()
  const path = useSelector(toolSelectors.path)
  const editedBadge = useSelector(selectors.data)

  const match = matchPath(location.pathname, {path: `${path}/:id`})

  useEffect(() => {
    if (get(match, 'params.id')) {
      dispatch(actions.reset(get(match, 'params.id')))
    }
  }, [get(match, 'params.id')])

  return (
    <Editor
      path={route({id: get(match, 'params.id')}, path)+'/edit'}
      title={get(editedBadge, 'name') || trans('badge', {}, 'badge')}
      name={selectors.FORM_NAME}
      target={(badge, isNew) => isNew ?
        ['apiv2_badge_create'] :
        ['apiv2_badge_update', {id: badge.id}]
      }
      close={route({id: get(match, 'params.id')}, path)}
      onSave={(savedData) => {
        dispatch(baseActions.openBadge(savedData.id))
      }}
      canAdministrate={hasPermission('administrate', editedBadge)}
      overviewPage={BadgeEditorOverview}
      appearancePage={BadgeEditorAppearance}
      permissionsPage={BadgeEditorPermissions}
      actionsPage={BadgeEditorActions}
      pages={[
        {
          name: 'attribution',
          title: trans('award_rules', {}, 'badge'),
          component: BadgeEditorAttribution
        }
      ]}
    />
  )
}

export {
  BadgeEditor
}
