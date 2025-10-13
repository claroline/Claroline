import React, {useCallback, useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor/components/main'

import {supportEvaluation} from '#/main/core/resource/utils'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'
import {ResourceEditorOverview} from '#/main/core/resource/editor/components/overview'
import {ResourceEditorPermissions} from '#/main/core/resource/editor/components/permissions'
import {ResourceEditorHistory} from '#/main/core/resource/editor/components/history'
import {ResourceEditorEvaluation} from '#/main/core/resource/editor/components/evaluation'
import {ResourceEditorActions} from '#/main/core/resource/editor/components/actions'

import {actions, selectors} from '#/main/core/resource/editor/store'
import {ResourceEditorSequences} from '#/main/core/resource/editor/components/sequences'
import {Thumbnail} from '#/main/app/components/thumbnail'

const ResourceEditor = ({
  overviewPage = ResourceEditorOverview,
  appearancePage = ResourceEditorAppearance,
  historyPage = ResourceEditorHistory,
  permissionsPage = ResourceEditorPermissions,
  evaluationPage = ResourceEditorEvaluation,
  pages = [],
  additionalData = () => ({}),
  styles = []
}) => {
  const resourcePath = useSelector(resourceSelectors.path)
  const resourceType = useSelector(resourceSelectors.resourceType)
  const resourceLoaded = useSelector(resourceSelectors.loaded)
  const resourceNode = useSelector(resourceSelectors.resourceNode)
  const resource = useSelector(resourceSelectors.resource)
  const editedNode = useSelector(selectors.resourceNode)

  const dispatch = useDispatch()
  const refresh = useCallback((resourceData) => dispatch(actions.refresh(resourceType, resourceData)), [resourceType])

  useEffect(() => {
    if (resourceLoaded) {
      const initialData = Object.assign({}, additionalData() || {}, {
        resourceNode: resourceNode,
        resource: resource,
        evaluation: resourceNode.evaluation
      })
      dispatch(actions.reset(initialData))
    }
  }, [get(resourceNode, 'id'), resourceLoaded])

  return (
    <Editor
      path={resourcePath+'/edit'}
      title={get(editedNode, 'name') || (resourceType && trans(resourceType, {}, 'resource')) || trans('resource')}
      styles={styles}
      name={resourceSelectors.EDITOR_NAME}
      target={['claro_resource_update', {id: get(resourceNode, 'id')}]}
      thumbnail={
        <Thumbnail
          className="rounded-1"
          thumbnail={editedNode.poster}
          name={editedNode.name}
          size="sm"
        />
      }
      close={resourcePath}
      onSave={refresh}
      canAdministrate={hasPermission('administrate', resourceNode || {})}
      overviewPage={overviewPage}
      appearancePage={appearancePage}
      historyPage={historyPage}
      permissionsPage={permissionsPage}
      actionsPage={ResourceEditorActions}
      pages={[
        {
          name: 'evaluation',
          title: trans('parameters'),
          help: trans('Activez le suivi pédagogique pour enregistrer et suivre la progression des utilisateurs.'),
          component: evaluationPage,
          disabled: !resourceLoaded || !supportEvaluation(resourceNode),
          group: trans('evaluation')
        }, {
          name: 'sequences',
          title: trans('Scénarisation'),
          help: trans('Retrouver tous les scénarios pédagogiques utilisant cette ressource.'),
          component: ResourceEditorSequences,
          displayed: false,
          disabled: !resourceLoaded || !supportEvaluation(resourceNode),
          group: trans('evaluation')
        }
      ].concat(pages || [])}
    />
  )
}

ResourceEditor.propTypes = {
  // standard pages
  overviewPage: T.elementType,
  appearancePage: T.elementType,
  historyPage: T.elementType,
  permissionsPage: T.elementType,
  evaluationPage: T.elementType,
  // custom pages
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string.isRequired,
    displayed: T.bool,
    disabled: T.bool,
    component: T.elementType,
    group: T.string
  })),

  /**
   * A func that returns some data to add to the Editor store on initialization.
   */
  additionalData: T.func,
  styles: T.array
}

export {
  ResourceEditor
}
