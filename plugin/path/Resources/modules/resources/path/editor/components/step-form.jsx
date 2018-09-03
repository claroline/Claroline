import React from 'react'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {selectors} from '#/plugin/path/resources/path/editor/store'

const SecondaryResourcesSection = props =>
  <div>
    {props.secondaryResources && props.secondaryResources.map(sr =>
      <ResourceCard
        className="step-secondary-resources"
        key={`secondary-resource-${sr.id}`}
        data={sr.resource}
        actions={[
          {
            name: 'delete',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            dangerous: true,
            modal: [MODAL_CONFIRM, {
              dangerous: true,
              icon: 'fa fa-fw fa-trash-o',
              title: trans('resources_delete_confirm'),
              question: trans('resource_delete_message'),
              handleConfirm: () => props.removeSecondaryResource(props.stepId, sr.id)
            }]
          }
        ]}
      />
    )}

    <button
      type="button"
      className="btn btn-default btn-block"
      onClick={() => props.pickSecondaryResources(props.stepId)}
    >
      <span className="fa fa-fw fa-plus icon-with-text-right"/>
      {trans('add_resources')}
    </button>
  </div>

SecondaryResourcesSection.propTypes = {
  stepId: T.string.isRequired,
  secondaryResources: T.array,
  pickSecondaryResources: T.func.isRequired,
  removeSecondaryResource: T.func.isRequired,
  updateSecondaryResourceInheritance: T.func.isRequired
}

const InheritedResourcesSection = props =>
  <div>
    {props.inheritedResources && props.inheritedResources.map(ir =>
      <div key={`inherited-resource-${ir.id}`}>
        {ir.resource.name} [{trans(ir.resource.meta.type, {}, 'resource')}]
        <span
          className="fa fa-fw fa-trash-o pointer-hand"
          onClick={() => props.removeInheritedResource(props.stepId, ir.id)}
        />
      </div>
    )}
  </div>

InheritedResourcesSection.propTypes = {
  stepId: T.string.isRequired,
  inheritedResources: T.array,
  removeInheritedResource: T.func.isRequired
}

const StepForm = props =>
  <FormData
    level={3}
    displayLevel={2}
    name={selectors.FORM_NAME}
    dataPart={props.stepPath}
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
        title: trans('information'),
        primary: true,
        fields: [
          {
            name: 'description',
            type: 'html',
            label: trans('content')
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
        icon: 'fa fa-fw fa-folder-open-o',
        title: trans('primary_resource', {}, 'path'),
        fields: [
          {
            name: 'primaryResource',
            type: 'resource',
            label: trans('resource'),
            options: {
              embedded: true,
              showHeader: true
            }
          },
          {
            name: 'showResourceHeader',
            type: 'boolean',
            label: trans('show_resource_header')
          }
        ]
      }
    ]}
  >

    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-folder-open-o"
        title={trans('secondary_resources', {}, 'path')}
      >
        <SecondaryResourcesSection
          stepId={props.id}
          secondaryResources={props.secondaryResources}
          pickSecondaryResources={props.pickSecondaryResources}
          removeSecondaryResource={props.removeSecondaryResource}
          updateSecondaryResourceInheritance={props.updateSecondaryResourceInheritance}
        />
      </FormSection>

      {props.inheritedResources.length > 0 &&
          <FormSection
            className="embedded-list-section"
            icon="fa fa-fw fa-folder-open-o"
            title={trans('inherited_resources', {}, 'path')}
          >
            <InheritedResourcesSection
              stepId={props.id}
              inheritedResources={props.inheritedResources}
              removeInheritedResource={props.removeInheritedResource}
            />
          </FormSection>
      }
    </FormSections>
  </FormData>

implementPropTypes(StepForm, StepTypes, {
  stepPath: T.string.isRequired,
  customNumbering: T.bool,
  pickSecondaryResources: T.func.isRequired,
  removeSecondaryResource: T.func.isRequired,
  updateSecondaryResourceInheritance: T.func.isRequired,
  removeInheritedResource: T.func.isRequired,
  saveForm: T.func.isRequired
}, {
  customNumbering: false
})


export {
  StepForm
}
