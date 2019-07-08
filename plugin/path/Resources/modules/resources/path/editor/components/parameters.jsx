import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/plugin/path/resources/path/constants'
import {selectors} from '#/plugin/path/resources/path/editor/store'

const EditorParameters = props =>
  <Fragment>
    <h3 className="h2 parameters-title">
      {constants.NUMBERING_NONE !== get(props.path, 'display.numbering') &&
        <span className="h-numbering">
          <span className="fa fa-cog" />
        </span>
      }

      {trans('parameters')}
    </h3>

    <FormData
      level={3}
      displayLevel={2}
      name={selectors.FORM_NAME}
      target={['apiv2_path_update', {id: props.path.id}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: '/',
        exact: true
      }}
      sections={[
        {
          icon: 'fa fa-fw fa-home',
          title: trans('overview'),
          fields: [
            {
              name: 'display.showOverview',
              type: 'boolean',
              label: trans('enable_overview'),
              linked: [
                {
                  name: 'display.description',
                  type: 'html',
                  label: trans('overview_message'),
                  displayed: props.path.display.showOverview,
                  options: {
                    workspace: props.workspace
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'display.manualProgressionAllowed',
              type: 'boolean',
              label: trans('path_manual_progression_allowed', {}, 'path')
            }, {
              name: 'display.numbering',
              type: 'choice',
              label: trans('path_numbering', {}, 'path'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.PATH_NUMBERINGS
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-sign-in',
          title: trans('opening_parameters'),
          fields: [
            {
              name: 'opening.secondaryResources',
              label: trans('secondary_resources_open_target', {}, 'path'),
              type: 'choice',
              options: {
                noEmpty: true,
                condensed: true,
                choices: {
                  _self: trans('same_window'),
                  _blank: trans('new_window')
                }
              }
            }
          ]
        }
      ]}
    />
  </Fragment>

EditorParameters.propTypes = {
  workspace: T.object,
  path: T.shape({
    id: T.string,
    display: T.shape({
      showOverview: T.bool
    })
  }).isRequired
}

export {
  EditorParameters
}
