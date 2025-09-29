import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {selectors as contextSelectors} from '#/main/app/context'
import {Tool, selectors as toolSelectors, route} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'

import {EvaluationEditor} from '#/main/evaluation/tools/evaluation/editor/components/main'
// import {EvaluationOverview} from '#/main/evaluation/tools/evaluation/components/overview'
import {EvaluationDashboard} from '#/main/evaluation/tools/evaluation/dashboard/containers/main'
import {EvaluationSequences} from '#/main/evaluation/tools/evaluation/components/sequences'
import {SequenceShow} from '#/main/evaluation/sequence/containers/show'

const EvaluationTool = (props) => {
  let currentSequence = {}
  if (!isEmpty(props.assignedSequences) && 1 === props.assignedSequences.length) {
    currentSequence = props.assignedSequences[0]
  }

  const loaded = useSelector(toolSelectors.loaded)
  const contextPath = useSelector(contextSelectors.path)
  const history = useHistory()

  useEffect(() => {
    if (loaded && !props.canFollow && !isEmpty(currentSequence)) {
      history.push(route('progression', contextPath)+'/sequences/'+currentSequence.id)
    }
  }, [loaded])

  return (
    <Tool
      {...omit(props, 'assignedSequences', 'canFollow')}
      menu={[
        {
          name: 'about',
          type: LINK_BUTTON,
          label: trans('my_progression'),
          target: props.path,
          exact: true,
          displayed: false
        }, {
          name: 'sequences',
          type: LINK_BUTTON,
          label: trans('sequences', {}, 'evaluation'),
          target: props.path/*+'/sequences'*/,
          displayed: 'workspace' === props.contextType,
          exact: true
        }
      ]}
      redirect={[
        {from: '', exact: true, to: '/sequences/'+(!isEmpty(currentSequence) ? currentSequence.id : ''), disabled: props.canFollow},
        {from: '', exact: true, to: '/sequences', disabled: !isEmpty(currentSequence) && !props.canFollow}
      ]}
      pages={[
        /*{
          path: '/',
          component: EvaluationOverview,
          exact: true
        }, */{
          path: '/sequences/:id',
          render: (routerProps) => <SequenceShow id={routerProps.match.params.id}  path={props.path + '/sequences'} />
        }, {
          path: '/sequences',
          component: EvaluationSequences,
          exact: true
        }
      ]}
      editor={EvaluationEditor}
      dashboard={EvaluationDashboard}
    />
  )
}

EvaluationTool.propTypes = {
  path: T.string.isRequired,
  canFollow: T.bool.isRequired,
  contextType: T.string.isRequired,
  assignedSequences: T.array
}

export {
  EvaluationTool
}
