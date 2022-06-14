import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {FormData} from '#/main/app/content/form/containers/data'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {selectors} from '#/plugin/path/resources/path/editor/store'

const EditorStep = props =>
  <Fragment>
    <ContentTitle
      className="step-title"
      level={3}
      displayLevel={2}
      numbering={props.numbering}
      title={props.title}
      actions={props.actions}
    />

    <FormData
      level={3}
      displayLevel={2}
      name={selectors.FORM_NAME}
      dataPart={props.stepPath}
      target={['apiv2_path_update', {id: props.pathId}]}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.basePath,
        exact: true
      }}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'description',
              type: 'html',
              label: trans('content'),
              options: {
                workspace: props.workspace
              }
            }, {
              name: 'primaryResource',
              type: 'resource',
              label: trans('resource'),
              options: {
                embedded: true,
                showHeader: true,
                picker: {
                  current : props.resourceParent
                }
              },
              linked: [
                {
                  name: 'evaluated',
                  type: 'boolean',
                  label: trans('evaluated', {}, 'path'),
                  help: trans('evaluated_help', {}, 'path'),
                  displayed: (step) => !isEmpty(step.primaryResource)
                }, {
                  name: 'showResourceHeader',
                  type: 'boolean',
                  label: trans('show_resource_header', {}, 'resource'),
                  displayed: (step) => !isEmpty(step.primaryResource)
                }
              ]
            }
          ]
        }, {
          title: trans('information'),
          icon: 'fa fa-fw fa-info',
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              type: 'image',
              label: trans('poster')
            }, {
              name: 'display.numbering',
              type: 'string',
              label: trans('step_numbering', {}, 'path'),
              displayed: props.customNumbering
            }
          ]
        }, {
          icon: 'fa fa-fw fa-folder',
          title: trans('secondary_resources', {}, 'path'),
          fields: [
            {
              name: 'secondaryResources',
              type: 'resources',
              label: trans('secondary_resources'),
              hideLabel: true,
              options: {
                picker: {
                  current : props.resourceParent
                }
              }
            }
          ]
        }
      ]}
    />
  </Fragment>

implementPropTypes(EditorStep, StepTypes, {
  basePath: T.string,
  workspace: T.object,
  pathId: T.string.isRequired,
  stepPath: T.string.isRequired,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  numbering: T.string,
  customNumbering: T.bool,

  // resources
  resourceParent: T.shape(
    ResourceNodeTypes.propTypes
  )
}, {
  customNumbering: false
})


export {
  EditorStep
}
