import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {ASYNC_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PageContent, PageSection, PageTabbedSection} from '#/main/app/page'
import {EmptyState} from '#/main/app/components/empty-state'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'

import {SequenceSummary} from '#/main/evaluation/sequence/components/summary'
import {SequencePage} from '#/main/evaluation/sequence/components/page'
import {EvaluationProgression} from '#/main/evaluation/components/progression'
import {EvaluationFeedback} from '#/main/evaluation/components/feedback'

import {selectors} from '#/main/evaluation/sequence/store'
import {constants} from '#/main/evaluation/constants'
import {Content} from '#/main/app/components/content'
import {SequenceResources} from '#/main/evaluation/sequence/components/resources'
import {SequenceObjective} from '#/main/evaluation/sequence/components/objective'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/sequence/modals/user-progression'

const SequenceOverviewContent = (props) => {
  const description = get(props.sequence, 'meta.descriptionHtml', null)
  const overviewResource = get(props.sequence, 'overview.resource')
  const objective = get(props.sequence, 'objective')
  const tags = get(props.sequence, 'tags')

  return (
    <>
      {(props.userEvaluation || props.actions) &&
        <PageSection
          className="mt-5 gap-4"
          title={trans('my_progression')}
          showTitle={false}
        >
          {props.userEvaluation &&
            <EvaluationProgression
              className="mb-4"
              evaluation={props.userEvaluation}
              modal={MODAL_USER_PROGRESSION}
            />
          }

          {props.userEvaluation && get(props, 'display.feedback', false) &&
            <div className="mb-4" role="presentation">
              <EvaluationFeedback
                status={props.userEvaluation.status}
                {...props.feedbacks}
              />
            </div>
          }

          {props.actions &&
            <Toolbar
              className="d-flex gap-1"
              buttonName="btn"
              primaryName="btn-primary"
              defaultName="btn-link"
              actions={props.actions}
              size="lg"
            />
          }
        </PageSection>
      }

      {(description || !isEmpty(tags) || overviewResource || objective) &&
        <PageSection
          title={trans('about')}
          showTitle={false}
          className="mt-5"
        >
          <Content
            tags={tags}
          >
            {description}
          </Content>

          {overviewResource &&
            <ResourceEmbedded
              className={!isEmpty(tags) || description ? 'mt-5' : undefined}
              resourceNode={overviewResource}
              showHeader={false}
            />
          }

          {objective &&
            <SequenceObjective className="mt-5" objective={objective} />
          }
        </PageSection>
      }
    </>
  )
}

const SequenceOverview = () => {
  const sequence = useSelector(selectors.sequence)
  const sequencePath = useSelector(selectors.path)
  const userEvaluation = useSelector(selectors.evaluation)
  const progression = useSelector(selectors.progression)
  const allSecondaryResources = useSelector(selectors.allSecondaryResources)

  const actions = [
    {
      name: 'start',
      type: LINK_BUTTON,
      label: trans('start', {}, 'actions'),
      target: `${sequencePath}/play`,
      primary: true,
      disabled: isEmpty(sequence.steps),
      displayed: !userEvaluation || [constants.EVALUATION_STATUS_UNKNOWN, constants.EVALUATION_STATUS_NOT_ATTEMPTED].includes(userEvaluation.status)
    }, {
      name: 'continue',
      type: LINK_BUTTON,
      label: trans('continue', {}, 'actions'),
      target: `${sequencePath}/play`,
      primary: true,
      disabled: isEmpty(sequence.steps),
      displayed: !!userEvaluation && constants.EVALUATION_STATUS_INCOMPLETE === userEvaluation.status
    }, {
      name: 'restart',
      type: LINK_BUTTON,
      label: trans('restart', {}, 'actions'),
      target: `${sequencePath}/play`,
      primary: true,
      disabled: isEmpty(sequence.steps),
      displayed: !!userEvaluation && [constants.EVALUATION_STATUS_PASSED, constants.EVALUATION_STATUS_FAILED, constants.EVALUATION_STATUS_COMPLETED].includes(userEvaluation.status)
    }, {
      name: 'download-certificate',
      type: ASYNC_BUTTON,
      label: trans('download_certificate', {}, 'actions'),
      request: {
        url: ['apiv2_sequence_download_certificate'],
        request: {
          method: 'POST',
          body: JSON.stringify([userEvaluation ? userEvaluation.id : null])
        }
      },
      displayed: !!userEvaluation && !!userEvaluation.certified && [constants.EVALUATION_STATUS_PASSED, constants.EVALUATION_STATUS_COMPLETED].includes(userEvaluation.status)
    }
  ]

  return (
    <SequencePage>
      <PageContent poster={get(sequence, 'poster')} className="pb-5">
        <SequenceOverviewContent
          sequence={sequence}
          actions={actions}
          userEvaluation={userEvaluation}
          display={{
            score: get(sequence, 'display.showScore'),
            scoreMax: get(sequence, 'evaluation.scoreTotal'),
            feedback: !!get(sequence, 'evaluation.successMessage') || !!get(sequence, 'evaluation.failureMessage')
          }}
          feedbacks={{
            success: get(sequence, 'evaluation.successMessage'),
            failure: get(sequence, 'evaluation.failureMessage')
          }}
        />

        {isEmpty(sequence.steps) &&
          <EmptyState
            size="lg"
            icon="fa fa-route"
            title={trans('no_step', {}, 'path')}
          />
        }

        {!isEmpty(sequence.steps) &&
          <PageTabbedSection
            className="mt-5"
            defaultTab="content"
            tabs={[
              {
                name: 'content',
                title: trans('content'),
                render: () =>
                  <SequenceSummary
                    className="mt-4"
                    path={sequencePath}
                    sequence={sequence}
                    progression={progression}
                  />
              }, {
                name: 'links',
                title: trans('useful_links'),
                displayed: !isEmpty(allSecondaryResources),
                render: () =>
                  <>
                    <p className="mt-4 text-body-secondary fs-sm">Retrouvez toutes les ressources complémentaires proposées au cours de la séquence.</p>
                    <SequenceResources
                      resources={allSecondaryResources}
                    />
                  </>
              }
            ]}
          />
        }
      </PageContent>
    </SequencePage>
  )
}

export {
  SequenceOverview
}
