import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/team/tools/team/store'
import {TeamParams as TeamParamsType} from '#/plugin/team/tools/team/prop-types'

const Editor = props =>
  <section className="tool-section">
    <h2>{trans('configuration', {}, 'platform')}</h2>
    <FormData
      level={3}
      name={selectors.STORE_NAME + '.teamParamsForm'}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.saveForm(props.teamParams.id)
      }}
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
              name: 'allowedTeams',
              type: 'number',
              label: trans('max_teams', {}, 'team'),
              options: {
                min: 0
              }
            }, {
              name: 'publicDirectory',
              type: 'boolean',
              label: trans('team_directory_public_access', {}, 'team')
            }, {
              name: 'deletableDirectory',
              type: 'boolean',
              label: trans('delete_team_directory', {}, 'team')
            }, {
              name: 'selfRegistration',
              type: 'boolean',
              label: trans('team_self_registration', {}, 'team')
            }, {
              name: 'selfUnregistration',
              type: 'boolean',
              label: trans('team_self_unregistration', {}, 'team')
            }
          ]
        }
      ]}
    />
  </section>

Editor.propTypes = {
  path: T.string.isRequired,
  teamParams: T.shape(TeamParamsType.propTypes).isRequired,
  saveForm: T.func.isRequired
}

export {
  Editor
}