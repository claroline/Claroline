import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import Waypoint, {Position} from '@restart/ui/Waypoint'

import {trans} from '#/main/app/intl/translation'
import {Content} from '#/main/app/components/content'
import {PageHeading, PageSection} from '#/main/app/page'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'

import {Step as StepTypes} from '#/main/evaluation/sequence/prop-types'
import {SequenceResources} from '#/main/evaluation/sequence/components/resources'
import {SequenceObjective} from '#/main/evaluation/sequence/components/objective'
import {selectors} from '#/main/evaluation/sequence/store'

const SecondaryResources = props =>
  <PageSection
    className="mb-5"
    title={trans('useful_links')}
  >
    <SequenceResources
      className="mb-0"
      resources={props.resources}
    />
  </PageSection>

SecondaryResources.propTypes = {
  resources: T.arrayOf(T.shape({
    // resource node type
  })).isRequired
}

/**
 * Renders step content.
 */
const Step = props => {
  const stepNumbering = useSelector(state => selectors.stepNumbering(state, props.step))

  const onScroll = useCallback((details) => {
    if (Position.INSIDE === details.position || Position.BEFORE === details.position) {
      props.updateProgression(props.step.id)
    }
  }, [props.step.id])

  return (
    <>
      {props.title &&
        <PageHeading
          level={props.level ? props.level + 1 : 1}
          poster={props.poster ? props.step.poster : undefined}
          title={stepNumbering ?
            stepNumbering + ' ' + props.step.title :
            props.step.title
          }
        />
      }

      {(props.step.description || props.step.estimatedDuration || props.step.objective) &&
        <PageSection className="mb-5">
          <Content
            meta={props.step.estimatedDuration &&
              <div role="presentation" aria-label={trans('estimated_duration')}>
                <span className="fa far fa-clock me-2" aria-hidden={true} />
                {props.step.estimatedDuration + ' ' + trans('minutes')}
              </div>
            }
          >
            {props.step.description}
          </Content>

          {props.step.objective &&
            <SequenceObjective className={classes({
              'mt-4': !props.step.description && props.step.estimatedDuration,
              'mt-5': props.step.description
            })} objective={props.step.objective} />
          }

          {((props.step.description || props.step.objective) && props.step.primaryResource) &&
            <hr className="mt-5 mb-0" aria-hidden={true} />
          }
        </PageSection>
      }

      {props.step.primaryResource &&
        <PageSection className="mb-5 flex-fill d-flex flex-column">
          <ResourceEmbedded
            resourceNode={props.step.primaryResource}
            showHeader={false}
            lifecycle={{
              play: props.disableNavigation,
              end: () => {
                props.enableNavigation()
                // get updated path progression
                props.updateProgression(props.step.id, true)
              }
            }}
          />
        </PageSection>
      }

      {0 !== props.step.secondaryResources.length &&
        <SecondaryResources
          resources={props.step.secondaryResources}
        />
      }

      <Waypoint
        key={props.step.id}
        onPositionChange={onScroll}
      />
    </>
  )
}

Step.propTypes = {
  level: T.number,
  poster: T.bool,
  title: T.bool,
  step: T.shape(StepTypes.propTypes),
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired
}

export {
  Step
}
