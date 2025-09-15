import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {scrollTo} from '#/main/app/dom/scroll'
import {Routes} from '#/main/app/router'
import {PageAside} from '#/main/app/page'

import {SequenceEvaluation as SequenceEvaluationTypes} from '#/main/evaluation/sequence/prop-types'
import {Sequence as SequenceTypes, Step as StepTypes} from '#/main/evaluation/sequence/prop-types'
import {SequencePage} from '#/main/evaluation/sequence/components/page'
import {route} from '#/main/evaluation/sequence/routing'

import {PlayerEnd} from '#/main/evaluation/sequence/player/components/end'
import {PlayerSummary} from '#/main/evaluation/sequence/player/components/summary'
import {PlayerModeInline} from '#/main/evaluation/sequence/player/components/mode-inline'
import {PlayerModeStep} from '#/main/evaluation/sequence/player/components/mode-step'
import {PlayerModeStepInline} from '#/main/evaluation/sequence/player/components/mode-step-inline'

const SequencePlayer = props => {
  const history = useHistory()
  const basePath = route(props.sequence, null, props.path)

  const updateProgression = useCallback((stepId) => {
    if (props.currentUser) {
      props.updateProgression(stepId)
    }
  }, [basePath, get(props.currentUser, 'id')])

  return (
    <SequencePage>
      <PageAside closable={true} show={false}>
        <PlayerSummary
          path={props.path}
          sequence={props.sequence}
          userEvaluation={props.evaluation}
          progression={props.progression}
        />
      </PageAside>

      <Routes
        path={props.path+'/play'}
        routes={[
          {
            path: '/end',
            component: PlayerEnd
          }, {
            path: '/:slug?',
            onEnter: (params) => {
              let step
              if (params.slug) {
                step = props.steps.find(step => params.slug === step.slug)
              } else {
                step = props.steps[0]
              }

              if (step) {
                props.setCurrentStep(step.slug)
                setTimeout(() => {
                  scrollTo('#step-'+step.id)
                }, 0)
              } else {
                history.replace(basePath)
              }
            },
            // force navigation in case the user as navigated with the summary without finishing an opened resource
            onLeave: () => props.enableNavigation,
            render: () => {
              switch (get(props.sequence, 'display.pagination', 'all')) {
                case 'none':
                  return (
                    <PlayerModeInline
                      path={props.path}
                      steps={props.steps}
                      updateProgression={updateProgression}
                      enableNavigation={props.enableNavigation}
                      disableNavigation={props.disableNavigation}
                    />
                  )

                case 'step':
                  return (
                    <PlayerModeStepInline
                      path={props.path}
                      steps={props.steps}
                      updateProgression={updateProgression}
                      enableNavigation={props.enableNavigation}
                      disableNavigation={props.disableNavigation}
                    />
                  )

                case 'all':
                default:
                  return (
                    <PlayerModeStep
                      path={props.path}
                      steps={props.steps}
                      updateProgression={updateProgression}
                      enableNavigation={props.enableNavigation}
                      disableNavigation={props.disableNavigation}
                    />
                  )
              }
            }
          }
        ]}
      />
    </SequencePage>
  )
}

SequencePlayer.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  sequence: T.shape(
    SequenceTypes.propTypes
  ).isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  progression: T.array,
  evaluation: T.shape(
    SequenceEvaluationTypes.propTypes
  ),
  setCurrentStep: T.func.isRequired,
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired
}

export {
  SequencePlayer
}
