import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/team/tools/team/store'
import {MultipleTeamForm as MultipleTeamFormComponent} from '#/plugin/team/tools/team/components/multiple-team-form'

const MultipleTeamForm = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state),
    form: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.teams.multiple')),
    resourceTypes: selectors.resourceTypes(state)
  })
)(MultipleTeamFormComponent)

export {
  MultipleTeamForm
}