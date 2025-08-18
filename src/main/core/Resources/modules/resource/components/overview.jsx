import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/app/utils/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Alert} from '#/main/app/components/alert'
import {Html} from '#/main/app/components/html'
import {Toolbar} from '#/main/app/action'
import {PageContent, PageSection} from '#/main/app/page'

import {ResourcePage} from '#/main/core/resource/components/page'
import {selectors} from '#/main/core/resource/store'

import {EvaluationFeedback} from '#/main/evaluation/components/feedback'
import {EvaluationProgression} from '#/main/evaluation/components/progression'

const ResourceOverview = props => {
  const path = useSelector(selectors.path)
  const resourceNode = useSelector(selectors.resourceNode)
  const showHeader = useSelector(selectors.showHeader)
  const embedded = useSelector(selectors.embedded)

  const userEvaluation = useSelector(selectors.userEvaluation)
  const description = get(resourceNode, 'meta.descriptionHtml', null)
  const estimatedDuration = get(resourceNode, 'estimatedDuration')

  return (
    <ResourcePage>
      <PageContent poster={showHeader ? get(resourceNode, 'poster') : undefined} className={classes('d-flex flex-column', {
        'mx-n4': embedded
      })}>
        {(userEvaluation || description || estimatedDuration || props.actions) &&
          <PageSection className={classes({
            'pt-5': !embedded || showHeader
          })}>
            {userEvaluation &&
              <EvaluationProgression
                className="mb-4"
                {...userEvaluation}
                target={path+'/progression'}
              />
            }

            {props.actions &&
              <Toolbar
                className="d-flex gap-1 mb-5"
                buttonName="btn"
                primaryName="btn-primary"
                defaultName="btn-link"
                actions={props.actions}
                size="lg"
              />
            }

            {description &&
              <Html className="content-text mb-5">{description}</Html>
            }
          </PageSection>
        }

        {userEvaluation &&
          <>
            {((!isEmpty(userEvaluation) && get(props, 'display.feedback', false)) || !isEmpty(get(props.feedbacks, 'closed'))) &&
              <PageSection className="resource-feedbacks py-3">
                {!isEmpty(userEvaluation) && get(props, 'display.feedback', false) &&
                  <EvaluationFeedback
                    status={userEvaluation.status}
                    {...props.feedbacks}
                  />
                }

                {!isEmpty(get(props.feedbacks, 'closed')) && props.feedbacks.closed.map(closedMessage =>
                  <Alert key={toKey(closedMessage[0])} type="warning" title={closedMessage[0]}>
                    <Html>{closedMessage[1]}</Html>
                  </Alert>
                )}
              </PageSection>
            }
          </>
        }

        {props.children}
      </PageContent>
    </ResourcePage>
  )
}

ResourceOverview.propTypes = {
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
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  children: T.node
}

ResourceOverview.defaultProps = {
  actions: []
}

export {
  ResourceOverview
}
