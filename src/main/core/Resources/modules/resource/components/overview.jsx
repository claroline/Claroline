import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {displayDate, trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {Alert} from '#/main/app/components/alert'

import {ResourcePage} from '#/main/core/resource/components/page'
import {selectors} from '#/main/core/resource/store'

import {constants} from '#/main/evaluation/resource/constants'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {EvaluationFeedback} from '#/main/evaluation/components/feedback'
import {EvaluationDetails} from '#/main/evaluation/components/details'
import {PageSection} from '#/main/app/page/components/section'
import {EvaluationJumbotron} from '#/main/evaluation/components/jumbotron'

const ResourceOverview = props => {
  const resourceNode = useSelector(selectors.resourceNode)

  const description = get(resourceNode, 'meta.descriptionHtml', null) || get(resourceNode, 'meta.description', null)

  return (
    <ResourcePage
      primaryAction={props.primaryAction}
      actions={props.actions}
      root={true}
    >
      {description &&
        <PageSection size="md" className="py-5">
          <ContentHtml className="lead">{description}</ContentHtml>
        </PageSection>
      }

      {props.evaluation &&
        <>
          <EvaluationJumbotron
            evaluation={props.evaluation}
          />

          {((!isEmpty(props.evaluation) && get(props, 'display.feedback', false)) || !isEmpty(get(props.feedbacks, 'closed'))) &&
            <PageSection size="md" className="resource-feedbacks py-3">
              {!isEmpty(props.evaluation) && get(props, 'display.feedback', false) &&
                <EvaluationFeedback
                  status={props.evaluation.status}
                  {...props.feedbacks}
                />
              }

              {!isEmpty(get(props.feedbacks, 'closed')) && props.feedbacks.closed.map(closedMessage =>
                <Alert key={toKey(closedMessage[0])} type="warning" title={closedMessage[0]}>
                  <ContentHtml>{closedMessage[1]}</ContentHtml>
                </Alert>
              )}
            </PageSection>
          }
        </>
      }

      {props.children}

      {0 !== props.actions.length &&
        <PageSection size="md" className="py-3">
          <h3 className="sr-only">{trans('resource_overview_actions', {}, 'resource')}</h3>

          <div className="d-grid gap-1" role="presentation">
            {props.actions
              .filter(action => undefined === action.displayed || action.displayed)
              .map((action, index) => !action.disabled ?
                  <Button
                    {...omit(action, 'disabledMessages')}
                    key={index}
                    className={classes('btn', {
                      'btn-outline-primary': !action.primary && !action.dangerous,
                      'btn-primary': action.primary,
                      'btn-danger': action.dangerous
                    })}
                    size={action.primary ? 'lg' : undefined}
                  /> :
                  action.disabledMessages && action.disabledMessages.map((message, messageIndex) =>
                    <Alert key={messageIndex} type="warning">{message}</Alert>
                  )
              )
            }
          </div>
        </PageSection>
      }
    </ResourcePage>
  )
}

ResourceOverview.propTypes = {
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  display: T.shape({
    score: T.bool,
    scoreMax: T.number,
    successScore: T.number,
    feedback: T.bool
  }),
  feedbacks: T.shape({
    success: T.string,
    failure: T.string,
    // a list of message to explain why the user can not submit new attempts to the resource (if quiz max attempts are reached, dropzone drop period finished, etc.)
    closed: T.arrayOf(T.array)
  }),
  statusTexts: T.object,

  /**
   * A list of detailed information about the evaluation.
   * Each info to display is an array of 2 elements : the first element is the label and the second is the associated value.
   */
  details: T.arrayOf(
    T.arrayOf(T.string)
  ),
  primaryAction: T.string,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  children: T.node
}

ResourceOverview.defaultProps = {
  primaryAction: 'start',
  actions: []
}

export {
  ResourceOverview
}
