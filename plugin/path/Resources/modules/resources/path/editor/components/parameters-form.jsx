import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/plugin/path/resources/path/constants'

const ParametersForm = props =>
  <FormData
    level={3}
    displayLevel={2}
    name="pathForm"
    title={trans('parameters')}
    className="content-container"
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm()
    }}
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
            label: trans('show_overview', {}, 'path'),
            linked: [
              {
                name: 'display.description',
                type: 'html',
                label: trans('overview_message', {}, 'path'),
                displayed: props.path.display.showOverview
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.showSummary',
            type: 'boolean',
            label: trans('show_summary', {}, 'path'),
            linked: [
              {
                name: 'display.openSummary',
                type: 'boolean',
                label: trans('show_opened_summary', {}, 'path'),
                displayed: props.path.display.showSummary
              }
            ]
          }, {
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
      }
    ]}
  />

ParametersForm.propTypes = {
  path: T.shape({
    display: T.shape({
      description: T.string,
      showOverview: T.bool.isRequired,
      showSummary: T.bool.isRequired,
      manualProgressionAllowed: T.bool.isRequired
    })
  }).isRequired,
  saveForm: T.func.isRequired
}

export {
  ParametersForm
}
