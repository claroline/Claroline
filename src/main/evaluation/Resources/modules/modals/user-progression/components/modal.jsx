import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {CloseButton} from 'react-bootstrap'
import get from 'lodash/get'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {useFetch} from '#/main/app/api/fetch'
import {displayDate, trans} from '#/main/app/intl'
import {ActionTypes, PromisedActionTypes} from '#/main/app/action'
import {Tab, Tabs} from '#/main/app/components/tabs'
import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'

import {UserEvaluation as UserEvaluationTypes} from '#/main/evaluation/prop-types'
import {UserProgressionInfo} from '#/main/evaluation/modals/user-progression/components/info'
import {UserProgressionDetails} from '#/main/evaluation/modals/user-progression/components/details'
import isEmpty from 'lodash/isEmpty'
import {Alert} from '#/main/app/components/alert'
import {Html} from '#/main/app/components/html'

const UserProgressionModal = (props) => {
  const [evaluationData] = useFetch(props.name, props.url)

  const tabs = [
    {
      name: 'overview',
      title: trans('overview'),
      displayed: !!props.overview,
      component: props.overview
    }, {
      name: 'archives',
      title: trans('Evaluations précédentes'),
      displayed: !!props.archives && !isEmpty(evaluationData) && !isEmpty(evaluationData.archives),
      component: props.archives
    }, {
      name: 'stats',
      title: trans('statistics'),
      displayed: !!props.stats,
      component: props.stats
    }
  ].concat(props.tabs || []).filter((tab) => undefined === tab.displayed || tab.displayed)

  return (
    <ModalEmpty
      {...omit(props, 'evaluation', 'actions', 'additional', 'title', 'name', 'overview')}
      size="xl"
    >
      <div className="d-flex flex-row" role="presentation">
        <UserProgressionDetails
          evaluation={props.evaluation}
          actions={props.actions}
          additional={props.additional}
        />

        <div className="flex-fill d-flex flex-column" role="presentation">
          <div className="modal-header">
            <UserProgressionInfo
              user={props.evaluation.user}
              title={props.title}
            />

            <CloseButton onClick={props.fadeModal} aria-label={trans('close', {}, 'actions')} />
          </div>

          <div className="modal-body pt-0">
            {get(props.evaluation, 'meta.archived', false) &&
              <Alert type="danger">
                <Html>{trans('evaluation_archived', {date: displayDate(get(props.evaluation, 'meta.archivedAt'))}, 'evaluation')}</Html>
              </Alert>
            }

            <Tabs className="mb-4" defaultActiveKey={tabs[0].name} variant="underline">
              {tabs.map(tab =>
                <Tab
                  key={tab.name}
                  eventKey={tab.name}
                  title={tab.title}
                >
                  {tab.component && createElement(tab.component, merge({}, evaluationData, {fadeModal: props.fadeModal}))}
                  {tab.render && tab.render()}
                </Tab>
              )}
            </Tabs>
          </div>
        </div>
      </div>
    </ModalEmpty>
  )
}

UserProgressionModal.propTypes = {
  name: T.string.isRequired,
  // the title of the activity (e.g., workspace, sequence, resource)
  title: T.string,
  // the api URL to fetch the user evaluation and progression
  url: T.oneOfType([T.string, T.array]).isRequired,
  evaluation: T.shape(
    UserEvaluationTypes.propTypes
  ).isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  additional: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    label: T.string.isRequired,
    value: T.any.isRequired
  })),
  tabs: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string.isRequired,
    displayed: T.bool,
    component: T.elementType
  })),
  overview: T.elementType.isRequired,
  stats: T.elementType,
  archives: T.elementType,
  fadeModal: T.func.isRequired
}

export {
  UserProgressionModal
}
