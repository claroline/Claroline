import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ToolPage, selectors as toolSelectors} from '#/main/core/tool'
import {EvaluationShortcut} from '#/main/evaluation/components/shortcut'

import {getActions} from '#/main/evaluation/sequence/utils'
import {selectors} from '#/main/evaluation/sequence/store'
import {MODAL_USER_PROGRESSION} from '#/main/evaluation/sequence/modals/user-progression'

const SequencePage = (props) => {
  const currentUser = useSelector(securitySelectors.currentUser)

  const toolPath = useSelector(toolSelectors.path)
  const sequence = useSelector(selectors.sequence)
  const sequencePath = useSelector(selectors.path)
  const userEvaluation = useSelector(selectors.evaluation)

  return (
    <ToolPage
      breadcrumb={[
        {
          label: sequence ? sequence.name : trans('loading'),
          target: sequencePath
        }
      ]}
      title={props.title ?
        props.title + ' | ' + sequence.name :
        trans('sequence_name', {name: get(sequence, 'name', trans('loading'))}, 'evaluation')
      }
      description={props.description || get(sequence, 'meta.description')}
      menu={{
        children: userEvaluation && (
          <EvaluationShortcut
            className="my-auto"
            modal={MODAL_USER_PROGRESSION}
            evaluation={userEvaluation}
          />
        ),
        toolbar: 'show-dashboard configure more',
        // get actions injected through plugins and the ones defined by the current tool
        actions: sequence ?
          getActions([sequence], {}, toolPath, currentUser, false)
          : []
      }}
      {...omit(props, 'breadcrumb', 'styles', 'embedded', 'showHeader', 'title', 'description')}
    >
      {props.children}
    </ToolPage>
  )
}

SequencePage.propTypes = {
  title: T.string,
  description: T.string,
  children: T.any
}

export {
  SequencePage
}
