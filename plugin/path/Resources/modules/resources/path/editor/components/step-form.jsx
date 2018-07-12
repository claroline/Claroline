import React from 'react'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'

import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const PrimaryResourceSection = props =>
  <div className="step-primary-resource">
    {!props.resource &&
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-folder"
        title={trans('no_primary_resource', {}, 'path')}
      >
        <button
          type="button"
          className="btn btn-primary btn-primary-resource btn-emphasis"
          onClick={() => props.pickPrimaryResource(props.stepId)}
        >
          <span className="fa fa-fw fa-plus icon-with-text-right"/>
          {trans('add_resource')}
        </button>
      </EmptyPlaceholder>
    }

    {props.resource &&
      <ResourceCard
        orientation="row"
        size="lg"
        data={props.resource}
        primaryAction={{
          type: 'url',
          label: trans('open', {}, 'actions'),
          target: ['claro_resource_open', {node: props.resource.autoId, resourceType: props.resource.meta.type}]
        }}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-folder-open',
            label: trans('replace', {}, 'actions'),
            callback: () => props.pickPrimaryResource(props.stepId)
          }, {
            type: 'callback',
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            dangerous: true,
            callback: () => props.removePrimaryResource(props.stepId)
          }
        ]}
      />
    }
  </div>

PrimaryResourceSection.propTypes = {
  stepId: T.string.isRequired,
  resource: T.shape({
    autoId: T.number.isRequired,
    id: T.string.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      type: T.string.isRequired
    }).isRequired
  }),
  pickPrimaryResource: T.func.isRequired,
  removePrimaryResource: T.func.isRequired
}

const SecondaryResourcesSection = props =>
  <div>
    {props.secondaryResources && props.secondaryResources.map(sr =>
      <div key={`secondary-resource-${sr.id}`}>
        {sr.resource.name} [{trans(sr.resource.meta.type, {}, 'resource')}]
        <span
          className={`fa fa-fw pointer-hand ${sr.inheritanceEnabled ? 'fa-eye-slash' : 'fa-eye'}`}
          onClick={() => props.updateSecondaryResourceInheritance(props.stepId, sr.id, !sr.inheritanceEnabled)}
        />
        <span
          className="fa fa-fw fa-trash-o pointer-hand"
          onClick={() => props.removeSecondaryResource(props.stepId, sr.id)}
        />
      </div>
    )}

    <button
      type="button"
      className="btn btn-default"
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
  <FormContainer
    level={3}
    displayLevel={2}
    name="pathForm"
    dataPart={props.stepPath}
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
      }
    ]}
  >
    <PrimaryResourceSection
      stepId={props.id}
      resource={props.primaryResource}
      pickPrimaryResource={props.pickPrimaryResource}
      removePrimaryResource={props.removePrimaryResource}
    />

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
  </FormContainer>

implementPropTypes(StepForm, StepTypes, {
  stepPath: T.string.isRequired,
  customNumbering: T.bool,
  pickPrimaryResource: T.func.isRequired,
  removePrimaryResource: T.func.isRequired,
  pickSecondaryResources: T.func.isRequired,
  removeSecondaryResource: T.func.isRequired,
  updateSecondaryResourceInheritance: T.func.isRequired,
  removeInheritedResource: T.func.isRequired
}, {
  customNumbering: false
})

export {
  StepForm
}
