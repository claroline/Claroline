
// main editor component
import {ResourceEditor} from '#/main/core/resource/editor/containers/main'

// standard editor pages
import {ResourceEditorActions} from '#/main/core/resource/editor/components/actions'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'
import {ResourceEditorEvaluation} from '#/main/core/resource/editor/components/evaluation'
import {ResourceEditorHistory} from '#/main/core/resource/editor/components/history'
import {ResourceEditorOverview} from '#/main/core/resource/editor/components/overview'
import {ResourceEditorPermissions} from '#/main/core/resource/editor/containers/permissions'

// store
import {selectors} from '#/main/core/resource/editor/store'

export {
  ResourceEditor,
  ResourceEditorActions,
  ResourceEditorAppearance,
  ResourceEditorEvaluation,
  ResourceEditorHistory,
  ResourceEditorOverview,
  ResourceEditorPermissions,
  selectors
}
