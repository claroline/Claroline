import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {PageBanner} from '#/main/app/page/components/banner'
import {ContextMain, selectors} from '#/main/app/context'
import {AppContext as AppContextTypes} from '#/main/app/context/prop-types'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceForbidden} from '#/main/app/contexts/workspace/containers/forbidden'
import {WorkspaceLoading} from '#/main/app/contexts/workspace/components/loading'
import {WorkspaceNotFound} from '#/main/app/contexts/workspace/components/not-found'
import {WorkspaceMenu} from '#/main/app/contexts/workspace/containers/menu'
import {WorkspaceEditor} from '#/main/app/contexts/workspace/editor/components/main'
import {addRecent} from '#/main/app/history'
import {hasPermission} from '#/main/app/security'

const WorkspaceWarning = () => {
  const workspace = useSelector(selectors.data)
  const impersonated = useSelector(selectors.impersonated)
  const roles = useSelector(selectors.roles)

  if (impersonated) {
    return (
      <PageBanner
        type="warning"
        content={`Vous parcourez l'espace d'activités avec les permissions du rôle <b>${roles[0] ? trans(roles[0].translationKey) : ''}</b>.`}
        dismissible={false}
        actions={[
          {
            name: 'change-role',
            type: CALLBACK_BUTTON,
            label: trans('Changer de rôle', {}, 'actions'),
            callback: () => true
          }, {
            name: 'exit',
            type: URL_BUTTON,
            label: trans('exit', {}, 'actions'),
            target: url(['claro_index', {}], {view_as: 'exit'}) + '#' + route(workspace)
          }
        ]}
      />
    )
  }

  if (get(workspace, 'meta.archived')) {
    return (
      <PageBanner
        type="danger"
        content="Cet espace est archivé et n'est plus accessible par les utilisateurs."
        actions={[
          {
            name: 'restore',
            type: CALLBACK_BUTTON,
            label: trans('restore', {}, 'actions'),
            callback: () => true,
            displayed: hasPermission('administrate', workspace),
            confirm: true
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            label: trans('Supprimer définitivement', {}, 'actions'),
            callback: () => true,
            displayed: hasPermission('administrate', workspace),
            confirm: true
          }
        ]}
      />
    )
  }

  if (get(workspace, 'meta.model')) {
    return (
      <PageBanner
        type="primary"
        content="Cet espace d'activités est un modèle utilisé pour la création de nouveaux espaces."
        actions={[
          {
            name: 'create',
            type: CALLBACK_BUTTON,
            label: trans('Créer à partir du modèle', {}, 'actions'),
            callback: () => true
          }
        ]}
      />
    )
  }
}

const WorkspaceContext = (props) =>
  <ContextMain
    {...props}
    menu={WorkspaceMenu}
    editor={WorkspaceEditor}
    loadingPage={WorkspaceLoading}
    notFoundPage={WorkspaceNotFound}
    forbiddenPage={WorkspaceForbidden}
    onOpen={(contextData) => {
      addRecent(contextData.id, 'workspace', route(contextData), contextData.name, get(contextData, 'meta.description'), contextData.thumbnail)
    }}
  >
    <WorkspaceWarning />
  </ContextMain>

WorkspaceContext.propTypes = AppContextTypes.propTypes
WorkspaceContext.defaultProps = AppContextTypes.defaultProps

export {
  WorkspaceContext
}
