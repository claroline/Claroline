import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {FormData} from '#/main/app/content/form/containers/data'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

import {constants} from '#/plugin/path/resources/path/constants'
import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'
import {selectors} from '#/plugin/path/resources/path/editor/store'

const EditorParameters = props =>
  <Fragment>
    <ContentTitle
      level={3}
      displayLevel={2}
      numbering={constants.NUMBERING_NONE !== get(props.path, 'display.numbering') ? <span className="fa fa-cog" /> : undefined}
      title={trans('parameters')}
    />

    <FormData
      level={3}
      displayLevel={2}
      name={selectors.FORM_NAME}
      target={['apiv2_path_update', {id: props.path.id}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.basePath,
        exact: true
      }}
      sections={[
        {
          icon: 'fa fa-fw fa-home',
          title: trans('overview'),
          fields: [
            {
              name: 'overview.display',
              type: 'boolean',
              label: trans('enable_overview'),
              linked: [
                {
                  name: 'overview.message',
                  type: 'html',
                  label: trans('overview_message'),
                  displayed: get(props.path, 'overview.display'),
                  options: {
                    workspace: props.workspace
                  }
                }, {
                  name: 'overview.resource',
                  type: 'resource',
                  label: trans('resource'),
                  options: {
                    picker: {
                      current : props.resourceParent
                    }
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
        }, {
          icon: 'fa fa-fw fa-flag-checkered',
          title: trans('end_page'),
          fields: [
            {
              name: 'end.display',
              type: 'boolean',
              label: trans('show_end_page'),
              linked: [
                {
                  name: 'end.message',
                  type: 'html',
                  label: trans('end_message'),
                  displayed: (path) => get(path, 'end.display'),
                  options: {
                    workspace: props.workspace
                  }
                }, {
                  name: 'end.navigation',
                  type: 'boolean',
                  label: trans('resource_end_navigation', {}, 'resource'),
                  displayed: (path) => get(path, 'end.display'),
                  linked: [
                    {
                      name: 'end.back._enabled',
                      type: 'boolean',
                      label: trans('resource_end_back', {}, 'resource'),
                      displayed: (path) => get(path, 'end.display') && get(path, 'end.navigation'),
                      calculated: (path) => !!get(path, 'end.back.type') || get(path, 'end.back._enabled'),
                      onChange: (enabled) => {
                        if (!enabled) {
                          props.update('end.back.type', null)
                          props.update('end.back.label', null)
                          props.update('end.back.target', null)
                        }
                      },
                      linked: [
                        {
                          name: 'end.back.label',
                          type: 'string',
                          label: trans('resource_end_back_label', {}, 'resource'),
                          placeholder: trans('return-home', {}, 'actions'),
                          displayed: (path) => get(path, 'end.display') && get(path, 'end.navigation') && (!!get(path, 'end.back.type') || get(path, 'end.back._enabled'))
                        }, {
                          name: 'end.back.type',
                          displayed: (path) => get(path, 'end.display') && get(path, 'end.navigation') && (!!get(path, 'end.back.type') || get(path, 'end.back._enabled')),
                          label: trans('resource_end_back_type', {}, 'resource'),
                          type: 'choice',
                          required: true,
                          options: {
                            choices: {
                              workspace: trans('resource_end_back_workspace', {}, 'resource'),
                              desktop: trans('resource_end_back_desktop', {}, 'resource'),
                              resource: trans('resource_end_back_resource', {}, 'resource')
                            }
                          },
                          linked: [
                            {
                              name: 'end.back.target',
                              type: 'resource',
                              required: true,
                              label: trans('resource'),
                              displayed: (path) => get(path, 'end.display') && get(path, 'end.navigation') && 'resource' === get(path, 'end.back.type')
                            }
                          ]
                        }
                      ]
                    }
                  ]
                }, {
                  name: 'end.workspaceCertificates',
                  type: 'boolean',
                  label: trans('resource_end_certificates', {}, 'resource'),
                  displayed: (path) => get(path, 'end.display')
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-percentage',
          title: trans('score'),
          fields: [
            {
              name: 'display.showScore',
              type: 'boolean',
              label: trans('show_score', {}, 'path')
            }, {
              name: 'score.total',
              label: trans('score_total'),
              type: 'number',
              required: true
            }
          ]
        }, {
          icon: 'fa fa-fw fa-award',
          title: trans('evaluation'),
          fields: [
            {
              name: 'score.success',
              label: trans('score_to_pass'),
              type: 'number',
              options: {
                min: 0,
                max: 100,
                unit: '%'
              },
              linked: [
                {
                  name: 'evaluation.successMessage',
                  label: trans('success_message'),
                  type: 'html',
                  displayed: (path) => !!get(path, 'score.success'),
                  options: {
                    workspace: props.workspace
                  }
                }, {
                  name: 'evaluation.failureMessage',
                  label: trans('failure_message'),
                  type: 'html',
                  displayed: (path) => !!get(path, 'score.success'),
                  options: {
                    workspace: props.workspace
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  </Fragment>

EditorParameters.propTypes = {
  basePath: T.string,
  workspace: T.object,
  resourceParent: T.shape(
    ResourceNodeTypes.propTypes
  ),
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  update: T.func.isRequired
}

export {
  EditorParameters
}
