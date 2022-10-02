import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ResourceCard} from '#/main/core/resource/components/card'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {constants} from '#/plugin/path/resources/path/constants'

const ManualProgression = props =>
  <div className="step-manual-progression">
    {trans('user_progression', {}, 'path')}

    <Button
      id="step-progression"
      className={classes('btn-link', props.status)}
      type={MENU_BUTTON}
      label={constants.STEP_STATUS[props.status]}
      menu={{
        align: 'right',
        items: Object.keys(constants.STEP_MANUAL_STATUS).map((status) => ({
          type: CALLBACK_BUTTON,
          label: constants.STEP_MANUAL_STATUS[status],
          callback: () => props.updateProgression(props.stepId, status, false)
        }))
      }}
    />
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
        key={resource.id}
        size="sm"
        orientation="row"
        primaryAction={{
          type: URL_BUTTON,
          label: trans('open', {}, 'actions'),
          target: '#'+resourceRoute(resource),
          open: props.target
        }}
        data={resource}
      />
    )}
  </div>

SecondaryResources.propTypes = {
  className: T.string,
  target: T.oneOf(['_self', '_blank']),
  resources: T.arrayOf(T.shape({
    // TODO : resource node type
  })).isRequired
}

/**
 * Renders step content.
 */
const Step = props =>
  <section className="current-step">
    {props.poster &&
      <img className="step-poster img-responsive" alt={props.title} src={asset(props.poster)} />
    }

    <h3 className="h2 h-title step-title">
      {props.numbering &&
        <span className="h-numbering">{props.numbering}</span>
      }

      {props.title}

      {props.manualProgressionAllowed && props.currentUser &&
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
          'col-md-9': 0 !== props.secondaryResources.length,
          'col-md-12': 0 === props.secondaryResources.length
        })}>
          {props.description &&
            <div className="step-description panel panel-default">
              <ContentHtml className="panel-body">{props.description}</ContentHtml>
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
            />
          }
        </div>
      }

      {0 !== props.secondaryResources.length &&
        <SecondaryResources
          className="col-md-3 col-sm-12"
          resources={props.secondaryResources}
          target={props.secondaryResourcesTarget}
        />
      }
    </div>
  </section>

implementPropTypes(Step, StepTypes, {
  currentUser: T.object,
  numbering: T.string,
  showResourceHeader: T.bool.isRequired,
  manualProgressionAllowed: T.bool.isRequired,
  secondaryResourcesTarget: T.oneOf(['_self', '_blank']),
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired
})

export {
  Step
}
