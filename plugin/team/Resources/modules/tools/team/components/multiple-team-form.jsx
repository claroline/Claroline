import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/team/tools/team/store'

const MultipleTeamForm = props =>
  <section className="tool-section">
    <h2>{trans('multiple_teams_creation', {}, 'team')}</h2>
    <FormData
      level={3}
      name={selectors.STORE_NAME + '.teams.multiple'}
      buttons={true}
      target={['apiv2_team_multiple_create', {workspace: props.workspace.uuid}]}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
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
              label: trans('description'),
              options: {
                workspace: props.workspace
              }
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
                condensed: false,
                inline: false,
                choices: props.resourceTypes.reduce((acc, type) => {
                  acc[type.name] = trans(type.name, {}, 'resource')

                  return acc
                }, {})
              }
            }
          ]
        }
      ]}
    />
  </section>

MultipleTeamForm.propTypes = {
  path: T.string.isRequired,
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
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired,
  resourceTypes: T.arrayOf(T.object).isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

export {
  MultipleTeamForm
}