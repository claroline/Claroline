import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {actions as formActions} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {TeamParams as TeamParamsType} from '#/plugin/team/tools/team/prop-types'

const EditorComponent = props =>
  <section className="tool-section">
    <h2>{trans('configuration', {}, 'platform')}</h2>
    <FormData
      level={3}
      name="teamParamsForm"
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.saveForm(props.teamParams.id)
      }}
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
              name: 'allowedTeams',
              type: 'number',
              label: trans('max_teams', {}, 'team'),
              options: {
                min: 0
              }
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
            }
          ]
        }
      ]}
    />
  </section>

EditorComponent.propTypes = {
  teamParams: T.shape(TeamParamsType.propTypes).isRequired,
  saveForm: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    teamParams: formSelectors.data(formSelectors.form(state, 'teamParamsForm'))
  }),
  (dispatch) => ({
    saveForm(id) {
      dispatch(formActions.saveForm('teamParamsForm', ['apiv2_workspaceteamparameters_update', {id: id}]))
    }
  })
)(EditorComponent)

export {
  Editor
}