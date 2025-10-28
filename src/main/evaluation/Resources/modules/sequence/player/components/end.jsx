import React from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON, LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {PageContent, PageSection} from '#/main/app/page'
import {Toolbar} from '#/main/app/action'
import {Html} from '#/main/app/components/html'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {constants} from '#/main/evaluation/constants'
import {selectors} from '#/main/evaluation/sequence/store'
import {EvaluationGauge} from '#/main/evaluation/components/gauge'
import {constants as sequenceConst} from '#/main/evaluation/sequence/constants'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/sequence/modals/user-progression'

const PlayerEnd = () => {
  const path = useSelector(selectors.path)
  const sequence = useSelector(selectors.sequence)
  const workspace = useSelector(selectors.workspace)
  const userEvaluation = useSelector(selectors.evaluation)
  const userFeedback = useSelector(selectors.userFeedback)

  return (
    <PageContent className="py-4 d-flex flex-column justify-content-center">
      {userEvaluation &&
        <PageSection className="py-4">
          <h2 className="visually-hidden">{trans('results')}</h2>
          <EvaluationGauge
            size="xl"
            className="mx-auto"
            {...userEvaluation}
          />
        </PageSection>
      }

      <PageSection className="py-4 text-center">
        <h2 className="visually-hidden">{trans('messages')}</h2>

        {!isHtmlEmpty(userFeedback) ?
          <Html className="content-text h4 mb-0">
            {userFeedback}
          </Html> :
          <p className="h4 mb-0">{sequenceConst.EVALUATION_STATUSES[userEvaluation.status || constants.EVALUATION_STATUS_UNKNOWN]}</p>
        }
      </PageSection>

      <PageSection className="py-4">
        <h2 className="visually-hidden">{trans('navigation')}</h2>

        <Toolbar
          className="d-flex align-items-start"
          buttonName="btn d-flex flex-column align-items-center text-uppercase text-wrap flex-fill w-25 focus-ring"
          defaultName="btn-text-body"
          primaryName="btn-text-body"
          actions={[
            {
              name: 'finish',
              type: LINK_BUTTON,
              displayed: !!userEvaluation && constants.EVALUATION_STATUS_INCOMPLETE === userEvaluation.status,
              icon: 'fa fa-fw fa-arrow-rotate-left me-0 mb-3 fs-2',
              label: trans('finish', {}, 'actions'),
              target: `${path}/continue`,
              primary: true
            }, {
              name: 'restart',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-arrow-rotate-left me-0 mb-3 fs-2',
              label: trans('restart', {}, 'actions'),
              target: `${path}/play`,
              exact: true,
              displayed: !!userEvaluation && constants.EVALUATION_STATUS_INCOMPLETE !== userEvaluation.status,
              primary: true
            }, {
              name: 'download-certificate',
              type: ASYNC_BUTTON,
              icon: 'fa fa-fw fa-download me-0 mb-3 fs-2',
              label: trans('download_certificate', {}, 'actions'),
              request: {
                url: ['apiv2_sequence_download_certificate'],
                request: {
                  method: 'POST',
                  body: JSON.stringify([userEvaluation ? userEvaluation.id : null])
                }
              },
              displayed: !!userEvaluation && !!userEvaluation.certified && [constants.EVALUATION_STATUS_PASSED, constants.EVALUATION_STATUS_COMPLETED].includes(userEvaluation.status)
            }, {
              name: 'show-results',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-check-double me-0 mb-3 fs-2',
              label: trans('show-results', {}, 'actions'),
              displayed: !!userEvaluation,
              modal: [MODAL_USER_PROGRESSION, {
                evaluation: userEvaluation
              }]
            }, {
              name: 'home',
              type: URL_BUTTON, // we require a URL_BUTTON here to escape the embedded resource router
              label: get(sequence, 'end.back.label') || trans('exit', {}, 'actions'),
              icon: 'fa fa-fw fa-times me-0 mb-3 fs-2',
              target: '#'+classes({
                [desktopRoute()]: 'desktop' === get(sequence, 'end.back.type'),
                [workspace ? workspaceRoute(workspace) : undefined]: 'workspace' === get(sequence, 'end.back.type'),
                [get(sequence, 'end.back.target') ? resourceRoute(get(sequence, 'end.back.target')) : undefined]: 'resource' === get(sequence, 'end.back.type')
              })
            }
          ]}
        />
      </PageSection>
    </PageContent>
  )
}

export {
  PlayerEnd
}
