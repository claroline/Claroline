import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {currentUser} from '#/main/app/security'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DropdownButton, MenuItem} from '#/main/core/layout/components/dropdown'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'
import {ResourceEmbedded} from '#/main/core/resource/components/embedded'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {constants} from '#/plugin/path/resources/path/constants'

const ManualProgression = props =>
  <div className="step-manual-progression">
    {trans('user_progression', {}, 'path')}

    <DropdownButton
      id="step-progression"
      title={constants.STEP_STATUS[props.status]}
      className={props.status}
      bsStyle="link"
      noCaret={true}
      pullRight={true}
    >
      {Object.keys(constants.STEP_MANUAL_STATUS).map((status) =>
        <MenuItem
          key={status}
          className={classes({
            active: status === props.status
          })}
          onClick={(e) => {
            props.updateProgression(props.stepId, status)

            e.preventDefault()
            e.stopPropagation()
            e.target.blur()
          }}
        >
          {constants.STEP_MANUAL_STATUS[status]}
        </MenuItem>
      )}
    </DropdownButton>
  </div>

ManualProgression.propTypes = {
  status: T.string.isRequired,
  stepId: T.string.isRequired,
  updateProgression: T.func.isRequired
}

const SecondaryResources = props =>
  <div className={classes('step-secondary-resources', props.className)}>
    <h4 className="h3 h-first">En complément...</h4>
    {props.resources.map(resource =>
      <ResourceCard
        key={resource.resource.id}
        size="sm"
        orientation="row"
        primaryAction={{
          type: 'url',
          label: trans('open', {}, 'actions'),
          target: ['claro_resource_open', {node: resource.resource.autoId, resourceType: resource.resource.meta.type}]
        }}
        data={resource.resource}
      />
    )}
  </div>

SecondaryResources.propTypes = {
  className: T.string,
  resources: T.arrayOf(T.shape({
    resource: T.shape({
      autoId: T.number.isRequired,
      meta: T.shape({
        type: T.string.isRequired
      }).isRequired
    }).isRequired
  })).isRequired
}

/**
 * Renders step content.
 */
const Step = props =>
  <section className="current-step">
    {props.poster &&
      <img className="step-poster img-responsive" alt={props.title} src={asset(props.poster.url)} />
    }

    <h3 className="h2 step-title">
      {props.numbering &&
        <span className="h-numbering">{props.numbering}</span>
      }

      {props.title}

      {props.manualProgressionAllowed && currentUser() &&
        <ManualProgression
          status={props.userProgression.status}
          stepId={props.id}
          updateProgression={props.updateProgression}
        />
      }
    </h3>

    <div className="row">
      {(props.primaryResource || props.description) &&
        <div className={classes('col-sm-12', {
          'col-md-9': (0 !== props.secondaryResources.length || 0 !== props.inheritedResources.length) && props.fullWidth,
          'col-md-12': (0 !== props.secondaryResources.length && 0 !== props.inheritedResources.length) && !props.fullWidth
        })}>
          {props.description &&
            <div className="panel panel-default">
              <HtmlText className="panel-body">{props.description}</HtmlText>
            </div>
          }

          {props.primaryResource &&
            <ResourceEmbedded
              className="step-primary-resource"
              resourceNode={props.primaryResource}
              showHeader={props.showResourceHeader}
              lifecycle={{
                play: props.disableNavigation,
                end: props.enableNavigation
              }}
              onResourceClose={props.onEmbeddedResourceClose}
            />
          }
        </div>
      }

      {(0 !== props.secondaryResources.length || 0 !== props.inheritedResources.length) &&
        <SecondaryResources
          className={classes('col-sm-12', {
            'col-md-3': props.fullWidth,
            'col-md-12': !props.fullWidth
          })}
          resources={[].concat(props.inheritedResources, props.secondaryResources)}
        />
      }
    </div>
  </section>

implementPropTypes(Step, StepTypes, {
  fullWidth: T.bool.isRequired,
  numbering: T.string,
  showResourceHeader: T.bool.isRequired,
  manualProgressionAllowed: T.bool.isRequired,
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired,
  onEmbeddedResourceClose: T.func
})

export {
  Step
}
