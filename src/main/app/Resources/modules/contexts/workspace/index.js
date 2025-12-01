import {declareContext} from '#/main/app/context'
import {WorkspaceContext} from '#/main/app/contexts/workspace/components/context'

export default declareContext('workspace', '/workspace/:contextId', WorkspaceContext)
