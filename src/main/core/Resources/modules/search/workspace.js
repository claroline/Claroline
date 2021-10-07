import {trans} from '#/main/app/intl'
import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {route} from '#/main/core/workspace/routing'

export default {
  name: 'workspace',
  label: trans('workspaces'),
  component: WorkspaceCard,
  link: route
}
