import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {selectors} from '#/plugin/path/resources/path/editor/store'

const EditorStep = props =>
  <Fragment>
    <h3 className="h2 step-title">
      {props.numbering &&
        <span className="h-numbering">{props.numbering}</span>
      }

      {props.title}

      <Toolbar
        id={props.id}
        className="step-toolbar"
        buttonName="btn"
        tooltip="bottom"
        toolbar="more"
        size="sm"
        actions={props.actions}
      />
    </h3>

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
              label: trans('poster'),
              options: {
                ratio: '3:1'
              }
            }, {
              name: 'display.numbering',
              type: 'string',
              label: trans('step_numbering', {}, 'path'),
              displayed: props.customNumbering
            }, {
              name: 'display.height',
              type: 'number',
              label: trans('step_content_height', {}, 'path'),
              options: {
                min: 0,
                unit: 'px',
                help: trans('step_content_height_info', {}, 'path')
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-folder',
          title: trans('primary_resource', {}, 'path'),
          fields: [
            {
              name: 'primaryResource',
              type: 'resource',
              label: trans('resource'),
              hideLabel: true,
              options: {
                embedded: true,
                showHeader: true,
                picker: {
                  current : props.resourceParent
                },
                onEmbeddedResourceClose: props.onEmbeddedResourceClose
              }
            }, {
              name: 'evaluated',
              type: 'boolean',
              label: trans('evaluated', {}, 'path'),
              help: trans('evaluated_help', {}, 'path'),
              displayed: (step) => !isEmpty(step.primaryResource)
            }, {
              name: 'showResourceHeader',
              type: 'boolean',
              label: trans('show_resource_header'),
              displayed: (step) => !isEmpty(step.primaryResource)
            }
          ]
        }, {
          icon: 'fa fa-fw fa-folder-o',
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
