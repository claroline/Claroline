import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {SkillsFrameworksModal} from '#/main/evaluation/modals/skills-frameworks/components/modal'
import {actions as formActions} from '#/main/app/content/form'
import {selectors} from '#/main/evaluation/skills-framework/modals/creation/store'
import {useDispatch} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

const CreationCopy = (props) => {
  const dispatch = useDispatch()

  return (
    <SkillsFrameworksModal
      {...omit(props, 'changeStep')}
      autoClose={false}
      selectAction={(selected) => ({
        type: CALLBACK_BUTTON,
        callback: () => {
          dispatch(formActions.load(selectors.STORE_NAME, cloneDeep(selected[0])))
          props.changeStep('form')
        }
      })}
      multiple={false}
    >
      <div className="modal-footer" role="presentation">
        <Button
          type={CALLBACK_BUTTON}
          label={trans('back')}
          className="btn btn-text-body me-auto"
          callback={() => props.changeStep('type')}
        />
      </div>
    </SkillsFrameworksModal>
  )
}

CreationCopy.propTypes = {
  changeStep: T.func.isRequired
}

export {
  CreationCopy
}
