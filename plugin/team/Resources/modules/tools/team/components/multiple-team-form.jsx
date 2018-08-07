import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'
import {select as workspaceSelect} from '#/main/core/workspace/selectors'

import {selectors} from '#/plugin/team/tools/team/store'

const MultipleTeamFormComponent = props =>
  <section className="tool-section">
    <h2>{trans('multiple_teams_creation', {}, 'team')}</h2>
    <FormData
      level={3}
      name="teams.multiple"
      buttons={true}
      target={['apiv2_team_multiple_create', {workspace: props.workspaceId}]}
      cancel={{
        type: LINK_BUTTON,
        target: '/',
        exact: true
      }}
      sections={[
        {
          id: 'general',
          icon: 'fa fa-fw fa-cogs',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'nbTeams',
              type: 'number',
              label: trans('nb_teams', {}, 'team'),
              required: true,
              options: {
                min: 1
              }
            }, {
              name: 'defaultResource',
              type: 'resource',
              label: trans('default_resource', {}, 'team')
            }, {
              name: 'maxUsers',
              type: 'number',
              label: trans('max_users', {}, 'team')
            }, {
              name: 'publicDirectory',
              type: 'boolean',
              label: trans('team_directory_public_access', {}, 'team'),
              required: true
            }, {
              name: 'deletableDirectory',
              type: 'boolean',
              label: trans('delete_team_directory', {}, 'team'),
              required: true
            }, {
              name: 'selfRegistration',
              type: 'boolean',
              label: trans('team_self_registration', {}, 'team'),
              required: true
            }, {
              name: 'selfUnregistration',
              type: 'boolean',
              label: trans('team_self_unregistration', {}, 'team'),
              required: true
            }, {
              name: 'creatableResources',
              type: 'choice',
              label: trans('user_creatable_resources', {}, 'team'),
              options: {
                multiple: true,
                condensed: true,
                choices: props.resourceTypes.reduce((acc, type) => {
                  acc[type] = trans(type, {}, 'resource')

                  return acc
                }, {})
              }
            }
          ]
        }
      ]}
    />
  </section>

MultipleTeamFormComponent.propTypes = {
  form: T.shape({
    name: T.string,
    description: T.string,
    nbTeams: T.number,
    selfRegistration: T.bool.isRequired,
    selfUnregistration: T.bool.isRequired,
    publicDirectory: T.bool.isRequired,
    deletableDirectory: T.bool.isRequired,
    maxUsers: T.number,
    defaultResource: T.object,
    creatableResources: T.arrayOf(T.string)
  }).isRequired,
  workspaceId: T.string.isRequired,
  resourceTypes: T.arrayOf(T.string).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

const MultipleTeamForm = connect(
  (state) => ({
    form: formSelectors.data(formSelectors.form(state, 'teams.multiple')),
    workspaceId: workspaceSelect.workspace(state).uuid,
    resourceTypes: selectors.resourceTypes(state)
  })
)(MultipleTeamFormComponent)

export {
  MultipleTeamForm
}